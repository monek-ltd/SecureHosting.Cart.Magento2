/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'mage/url',
    ],
    function (Component, placeOrderAction, url) {
        'use strict';
        
        return Component.extend({
            defaults: {
                template: 'Monek_SecureHosting/payment/securehosting'
            },
            afterPlaceOrder: function () {
                window.location.replace(url.build('securehosting/redirect/'));
            },
        });
    }
);
