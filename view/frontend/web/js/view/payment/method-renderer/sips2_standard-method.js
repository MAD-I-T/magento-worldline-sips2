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
                template: 'Madit_Sips2/payment/sips2_standard'
            },
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {

                window.sips2maditRedirectUrl = 'sips2madit/payment/redirect';
                this.isChecked.subscribe(function (code) {
                    if(code === 'sips2_standard'){
                        window.sips2maditRedirectUrl = 'sips2madit/payment/redirect';
                    }else {
                        window.sips2maditRedirectUrl = false;
                    }
                });

            }
        });
    }
);
