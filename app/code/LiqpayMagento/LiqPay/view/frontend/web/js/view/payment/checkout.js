/**
 * LiqPay Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'liqpaymagento_liqpay',
                component: 'LiqpayMagento_LiqPay/js/view/payment/method-renderer/liqpay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);