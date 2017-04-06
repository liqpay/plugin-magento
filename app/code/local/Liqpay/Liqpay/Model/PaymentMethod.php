<?php
/**
 * Liqpay Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category        Liqpay
 * @package         Liqpay_Liqpay
 * @version         3.0
 * @author          Liqpay
 * @copyright       Copyright (c) 2014 Liqpay
 * @license         http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * EXTENSION INFORMATION
 *
 * Magento          Community Edition 1.8.1.0
 * LiqPay API       Click&Buy 1.2 (https://www.liqpay.com/ru/doc)
 * Way of payment   Visa / MasterCard, or LiqPay
 *
 */


/**
 * Payment method liqpay model
 *
 * @author      Liqpay <support@liqpay.com>
 */
class Liqpay_Liqpay_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Payment Method features
     * @var bool
     */
    protected $_canCapture             = true;
    protected $_canVoid                = true;
    protected $_canUseForMultishipping = false;
    protected $_canUseInternal         = false;
    protected $_isInitializeNeeded     = true;
    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canUseCheckout          = true;

    protected $_code = 'liqpay';
    protected $_formBlockType = 'liqpay/paymentInformation';
    protected $_allowCurrencyCode = array('EUR','UAH','USD','RUB','RUR');
    protected $_order;


    /**
    * Возвращает набор полей необходимых для передачи
    *
    * @return array
    */
    public function getRedirectFormFields()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

        if (!$order->getId()) {
            return array();
        }

        $private_key = $this->getConfigData('liqpay_private_key');
        $public_key  = $this->getConfigData('liqpay_public_key');
        $amount      = $order->getGrandTotal();
        $currency    = $order->getOrderCurrencyCode();

        if ($currency == 'RUR') {
            $currency = 'RUB';
        }

        $order_id    = $order->getIncrementId();
        $description = Mage::helper('liqpay')->__('Order') . ' №' . $order_id;
        $result_url  = Mage::getUrl('liqpay/payment/result');
        $server_url  = Mage::getUrl('liqpay/payment/server');
        // $type        = 'buy';
        $action      = 'pay';
        $version     = '3';
        $language    = $this->getConfigData('language');
        $sandbox     = $this->getConfigData('sandbox');

        $request = array(
            'version'     => $version,
            'public_key'  => $public_key,
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => $description,
            'order_id'    => $order_id,
            // 'type'        => $type,
            'action'      => $action,
            'language'    => $language,
            'result_url'  => $result_url,
            'server_url'  => $server_url
        );

        if ($sandbox) {
            $request['sandbox'] = 1;
        }

        $this->_debug(array(
            'url' => $this->getLiqpayPlaceUrl(),
            'request' => $request
        ));

        $data = base64_encode(json_encode($request));

        $signature = base64_encode(sha1($private_key . $data . $private_key, 1));

        return compact('data', 'signature');
    }


    /**
    * Get redirect url.
    * Return Order place redirect url.
    *
    * @return string
    */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('liqpay/payment/redirect', array('_secure' => true));
    }


    /**
     * Return Liqpay place URL
     *
     * @return string
     */
    public function getLiqpayPlaceUrl()
    {
        return $this->getConfigData('liqpay_action');
    }


    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_NEW;
        $stateObject->setState($state);
        $stateObject->setStatus(Mage::getSingleton('sales/order_config')->getStateDefaultStatus($state));
        $stateObject->setIsNotified(false);
        return $this;
    }


    /**
     * Validate data from LiqPay server and update the database
     *
     * @var array $post
     *
     * @return void
     */
    public function processNotification($post)
    {

        $success =
            isset($post['data']) &&
            isset($post['signature']);

        if (!$success) {
			$this->_debug(array(
				'response' => $post
	        ));
            Mage::throwException(Mage::helper('liqpay')->__('Data or signature is empty'));
        }

        $data         = $post['data'];
        $decoded_data = base64_decode($data);

        $parsed_data = json_decode($decoded_data, true, 1024);

        $this->_debug(array(
            'parsed_response' => $parsed_data
        ));

        $received_signature  = $post['signature'];
        $received_public_key = $parsed_data['public_key'];
        $order_id            = $parsed_data['order_id'];
        $status              = $parsed_data['status'];
        $sender_phone        = $parsed_data['sender_phone'];
        $amount              = $parsed_data['amount'];
        $currency            = $parsed_data['currency'];
        $transaction_id      = $parsed_data['transaction_id'];

        if ($order_id <= 0) {
            Mage::throwException(Mage::helper('liqpay')->__('Order id is not set'));
        }

		/** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($order_id);

        if (!$order->getId()) {
            Mage::throwException(Mage::helper('liqpay')->__('Cannot load order'));
        }

        $private_key = $this->getConfigData('liqpay_private_key');
        $public_key  = $this->getConfigData('liqpay_public_key');

        $generated_signature = base64_encode(sha1($private_key . $data . $private_key, 1));

        if ($received_signature != $generated_signature || $public_key != $received_public_key) {
            $order->addStatusHistoryComment(Mage::helper('liqpay')->__('Security check failed!'));
            $order->save();
            return;
        }

		$status = 'liqpay_' . $status;
		$statusStates = $order->getConfig()->getStatusStates( $status );
		if ( empty( $statusStates ) ) {
			$allStatuses = $order->getConfig()->getStatuses();
			if ( !array_key_exists( $status, $allStatuses ) ) {
				$model = Mage::getModel('sales/order_status')->load( $status );
				$model->setStatus( $status );
				$model->setLabel( $status . ': see details at https://www.liqpay.com/ru/doc/callback' );
				$model->save();
			}
			$state = Mage_Sales_Model_Order::STATE_HOLDED;
		} else {
			$state = array_shift( $statusStates )->getState();
		}

        $this->_debug(array( 'state' => $state ));

		if ( $state != Mage_Sales_Model_Order::STATE_PROCESSING ) {
			$order->setState( $state, $status, $order->getConfig()->getStatusLabel( $status ), $notified = false );
			$order->save();
			return;
		}

		if ( !$order->canInvoice() ) {
			$order->addStatusHistoryComment(Mage::helper('liqpay')->__('Error during creation of invoice.'))
				->setIsCustomerNotified($notified = false);
			return;
		}

		$order->getPayment()->setTransactionId($transaction_id);
		$invoice = $order->prepareInvoice();
		$invoice->register()->pay();
		Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();

		if (!$this->getConfigData('sandbox')) {
			$message = Mage::helper('liqpay')->__(
				'Invoice #%s created.',
				$invoice->getIncrementId()
			);
		} else {
			$message = Mage::helper('liqpay')->__(
				'Invoice #%s created (sandbox).',
				$invoice->getIncrementId()
			);
		}

		$order->setState( $state, $status, $message, $notified = false );

		$sDescription = '';
		$sDescription .= 'sender phone: ' . $sender_phone . '; ';
		$sDescription .= 'amount: ' . $amount . '; ';
		$sDescription .= 'currency: ' . $currency . '; ';

		$order->addStatusHistoryComment($sDescription)->setIsCustomerNotified($notified);
		$order->save();
		$this->_debug(array( 'order' => 'saved' ));
    }
}
