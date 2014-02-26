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
* @version         0.0.1
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
* Payment method liqpay controller
*
* @author      Liqpay <support@liqpay.com>
*/
class Liqpay_Liqpay_PaymentController extends Mage_Core_Controller_Front_Action
{

    /**
     * Order
     * @var
     */
    protected $_order;

    /**
     *
     * Redirect customer to Liqpay payment interface
     *
     */
    public function redirectAction()
    {
        $session = $this->getSession();

        $quote_id = $session->getQuoteId();
        $last_real_order_id = $session->getLastRealOrderId();

        if (is_null($quote_id) || is_null($last_real_order_id)) {
            $this->_redirect('checkout/cart/');
        } else {
            $session->setLiqpayQuoteId($quote_id);
            $session->setLiqpayLastRealOrderId($last_real_order_id);

            $order = $this->getOrder();
            $order->loadByIncrementId($last_real_order_id);

            $html = $this->getLayout()->createBlock('liqpay/redirect')->toHtml();
            $this->getResponse()->setHeader('Content-type', 'text/html; charset=windows-1251')->setBody($html);

            $order->addStatusToHistory(
                $order->getStatus(),
                Mage::helper('liqpay')->__('Customer switch over to Liqpay payment interface.')
            )->save();

            $session->getQuote()->setIsActive(false)->save();

            $session->setQuoteId(null);
            $session->setLastRealOrderId(null);
        }
    }



    /**
     * Customer successfully got back from LiqPay payment interface
     */
    public function resultAction()
    {
        $session = Mage::getSingleton('checkout/session');

        $order_id = $session->getLiqpayLastRealOrderId();
        $quote_id = $session->getLiqpayQuoteId(true);

        $order = $this->getOrder();
        $order->loadByIncrementId($order_id);

        if ($order->isEmpty()) {
            return false;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('liqpay')->__('Customer successfully got back from Liqpay payment interface.')
        )->save();

        $session->setQuoteId($quote_id);
        $session->getQuote()->setIsActive(false)->save();
        $session->setLastRealOrderId($order_id);

        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    /**
     *
     * Validate data from Liqpay server and update the database
     *
     */
    public function serverAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->norouteAction();
            return;
        }
        $this->getLiqpay()->processNotification($this->getRequest()->getPost());
    }


    /**
     * Session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }


    /**
     * Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
            $session = $this->getSession();
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }


    /**
     *
     *
     * @return Liqpay_Liqpay_Model_PaymentMethod
     */
    public function getLiqpay()
    {
        return Mage::getSingleton('liqpay/paymentMethod');
    }

}