<?php

/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace LiqpayMagento\LiqPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use LiqpayMagento\LiqPay\Model\Payment as LiqPayPayment;
use Magento\Payment\Helper\Data as PaymentHelper;


class Data extends AbstractHelper
{
    const XML_PATH_IS_ENABLED  = 'payment/liqpaymagento_liqpay/active';
    const XML_PATH_PUBLIC_KEY  = 'payment/liqpaymagento_liqpay/public_key';
    const XML_PATH_PRIVATE_KEY = 'payment/liqpaymagento_liqpay/private_key';
    const XML_PATH_TEST_MODE = 'payment/liqpaymagento_liqpay/sandbox';
    const XML_PATH_TEST_ORDER_SURFIX = 'payment/liqpaymagento_liqpay/sandbox_order_surfix';
    const XML_PATH_DESCRIPTION = 'payment/liqpaymagento_liqpay/description';
    const XML_PATH_CALLBACK_SECURITY_CHECK = 'payment/liqpaymagento_liqpay/security_check';
    /**
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    public function __construct(Context $context,
                                PaymentHelper $paymentHelper)
    {
        parent::__construct($context);
        $this->_paymentHelper = $paymentHelper;
    }


    public function isEnabled()
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            if ($this->getPublicKey() && $this->getPrivateKey()) {
                return true;
            } else {
                $this->_logger->error(__('The LiqpayMagento\LiqPay module is turned off, because public or private key is not set'));
            }
        }
        return false;
    }

    public function isTestMode()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TEST_MODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isSecurityCheck()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALLBACK_SECURITY_CHECK,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPublicKey()
    {
        return trim($this->scopeConfig->getValue(
            self::XML_PATH_PUBLIC_KEY,
            ScopeInterface::SCOPE_STORE
        ));
    }

    public function getPrivateKey()
    {
        return trim($this->scopeConfig->getValue(
            self::XML_PATH_PRIVATE_KEY,
            ScopeInterface::SCOPE_STORE
        ));
    }

    public function getTestOrderSurfix()
    {
        return trim($this->scopeConfig->getValue(
            self::XML_PATH_TEST_ORDER_SURFIX,
            ScopeInterface::SCOPE_STORE
        ));
    }

    public function getLiqPayDescription(\Magento\Sales\Api\Data\OrderInterface $order = null)
    {
        $description = trim($this->scopeConfig->getValue(
            self::XML_PATH_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        ));
        $params = [
            '{order_id}' => $order->getIncrementId(),
        ];
        return strtr($description, $params);
    }

    public function checkOrderIsLiqPayPayment(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $method = $order->getPayment()->getMethod();
        $methodInstance = $this->_paymentHelper->getMethodInstance($method);
        return $methodInstance instanceof LiqPayPayment;
    }

    public function securityOrderCheck($data, $receivedPublicKey, $receivedSignature)
    {
        if ($this->isSecurityCheck()) {
            $publicKey = $this->getPublicKey();
            if ($publicKey !== $receivedPublicKey) {
                return false;
            }
            
            $privateKey = $this->getPrivateKey();
            $generatedSignature = base64_encode(sha1($privateKey . $data . $privateKey, 1));
            
            return $receivedSignature === $generatedSignature;
        } else {
            return true;
        }
    }

    public function getLogger()
    {
        return $this->_logger;
    }
}
