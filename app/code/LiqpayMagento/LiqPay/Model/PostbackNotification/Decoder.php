<?php
/**
 * LiqpayMagento_LiqPay
 *
 * @category    LiqpayMagento
 * @package     LiqpayMagento_LiqPay
 * @copyright   Copyright (c) 2020
 * @author      Arthur Agratina
 */

namespace LiqpayMagento\LiqPay\Model\PostbackNotification;

use LiqpayMagento\LiqPay\Api\DecoderInterface;
/**
 * Class Decoder
 * @package LiqpayMagento\LiqPay\Model\PostbackNotification
 */
class Decoder implements DecoderInterface
{
    /**s
     * Decodes the given $data string which is encoded in the x-www-form-urlencoded format.
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        parse_str($data, $result);

        return $result;
    }
}
