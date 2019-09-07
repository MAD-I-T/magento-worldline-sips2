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
                template: 'Madit_Atos/payment/atos_standard'
            },
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {

                window.sherlockRedirectUrl = 'sherlock/payment/redirect';
                this.isChecked.subscribe(function (code) {
                    if(code === 'atos_standard'){
                        window.sherlockRedirectUrl = 'sherlock/payment/redirect';
                    }else {
                        window.sherlockRedirectUrl = false;
                    }
                });

            }
        });
    }
);
