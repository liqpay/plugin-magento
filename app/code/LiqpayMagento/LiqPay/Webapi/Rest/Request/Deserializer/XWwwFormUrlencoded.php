<?php
/**
 * LiqpayMagento_LiqPay
 *
 * @category    LiqpayMagento
 * @package     LiqpayMagento_LiqPay
 * @copyright   Copyright (c) 2020
 * @author      Arthur Agratina
 */

namespace LiqpayMagento\LiqPay\Webapi\Rest\Request\Deserializer;

use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;

/**
 * Class XWwwFormUrlencoded
 * @package LiqpayMagento\LiqPay\WebApi\Rest\Request\Deserializer
 */
class XWwwFormUrlencoded implements DeserializerInterface
{

    /** @var \LiqpayMagento\LiqPay\Model\PostbackNotification\Decoder */
    protected $decoder;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @param \LiqpayMagento\LiqPay\Model\PostbackNotification\Decoder $decoder
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\LiqpayMagento\LiqPay\Model\PostbackNotification\Decoder $decoder, State $appState)
    {
        $this->decoder = $decoder;
        $this->_appState = $appState;
    }

    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     * @return array|null Return NULL if content is invalid.
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Webapi\Exception If decoding error was encountered.
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($encodedBody))
            );
        }
        try {
            $decodedBody = $this->decoder->decode($encodedBody);
        } catch (\Zend_Json_Exception $e) {
            if ($this->_appState->getMode() !== State::MODE_DEVELOPER) {
                throw new \Magento\Framework\Webapi\Exception(new Phrase('Decoding error.'));
            } else {
                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase(
                        'Decoding error: %1%2%3%4',
                        [PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString()]
                    )
                );
            }
        }
        return $decodedBody;
    }
}
