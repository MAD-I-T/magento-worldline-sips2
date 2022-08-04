/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'mage/url'
    ],
    function (url) {
        'use strict';


        return {
            redirectUrl: window.checkoutConfig.defaultSuccessPageUrl,

            /**
             * Provide redirect to page
             */
            execute: function (){
                if(window.sips2maditRedirectUrl){
                    window.location.replace(url.build(window.sips2maditRedirectUrl));
                }else{
                    window.location.replace(url.build(this.redirectUrl));
                }
            }
        };
    }
);
