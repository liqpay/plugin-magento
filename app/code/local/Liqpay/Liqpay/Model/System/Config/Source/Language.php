<?php

class Liqpay_Liqpay_Model_System_Config_Source_Language
{
    public function toOptionArray()
    {
        $helper = Mage::helper('liqpay');
        return array(
            'en' => $helper->__('English'),
            'ru' => $helper->__('Russian'),
        );
    }
}