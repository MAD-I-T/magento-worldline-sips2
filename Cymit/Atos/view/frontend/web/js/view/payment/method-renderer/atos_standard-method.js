/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (ko, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Cymit_Atos/payment/atos_standard'
            },
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {
                //window.checkoutConfig.defaultSuccessPageUrl = 'sherlock/payment/redirect';
                //console.log("try to find instructions:"+this.item.method);
                //return window.checkoutConfig.payment.instructions[this.item.method];
                this.isChecked.subscribe(function (code) {

                    console.log('selected payment method code is: ', code);
                    if(code === 'atos_standard'){
                        window.sherlockRedirectUrl = 'sherlock/payment/redirect';
                    }else {
                        window.sherlockRedirectUrl = false;
                    }
                })
            }
        });
    }
);
