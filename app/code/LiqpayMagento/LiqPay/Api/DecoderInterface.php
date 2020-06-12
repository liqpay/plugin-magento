<?php
/**
 * LiqpayMagento_LiqPay
 *
 * @category    LiqpayMagento
 * @package     LiqpayMagento_LiqPay
 * @copyright   Copyright (c) 2020
 * @author      Arthur Agratina
 */

namespace LiqpayMagento\LiqPay\Api;

/**
 * JSON decoder
 *
 * @api
 */
interface DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the x-www-form-urlencoded format into a PHP type (array, string literal, etc.)
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data);
}