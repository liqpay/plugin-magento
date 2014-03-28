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
 * Payment method liqpay redirect
 *
 * @author      Liqpay <support@liqpay.com>
 */
class Liqpay_Liqpay_Block_Redirect extends Mage_Core_Block_Template
{

    /**
     * Set template with message
     */
    protected function _construct()
    {
        $this->setTemplate('liqpay/redirect.phtml');
        parent::_construct();
    }


    /**
     * Return redirect form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        $paymentMethod = Mage::getModel('liqpay/paymentMethod');

        //$form = new Form();
        $form = new Varien_Data_Form();
        $form->setAction($paymentMethod->getLiqpayPlaceUrl())
             ->setId('liqpay_redirect')
             ->setName('liqpay_redirect')
             ->setData('accept-charset', 'utf-8')
             ->setUseContainer(true)
             ->setMethod('POST');

        foreach ($paymentMethod->getRedirectFormFields() as $field=>$value) {
            $form->addField($field,'hidden',array('name'=>$field,'value'=>$value));
        }
        return $form;
    }
}