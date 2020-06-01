<?php
/**
 * LiqpayMagento_LiqPay
 *
 * @category    LiqpayMagento
 * @package     LiqpayMagento_LiqPay
 * @copyright   Copyright (c) 2020
 * @author      Arthur Agratina
 */

namespace LiqpayMagento\LiqPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use LiqpayMagento\LiqPay\Api\Data\PaymentLanguageInterface;

/**
 * Class PaymentAction
 * @package LiqpayMagento\LiqPay\Model\Config\Source
 */
class PaymentLanguage implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => PaymentLanguageInterface::EN, 'label' => __('English')],
            ['value' => PaymentLanguageInterface::RU, 'label' => __('Russian')],
            ['value' => PaymentLanguageInterface::UA, 'label' => __('Ukrainian')],
        ];
    }
}