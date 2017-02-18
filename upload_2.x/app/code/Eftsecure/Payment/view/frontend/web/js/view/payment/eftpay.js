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
                type: 'eftpay',
                component: 'Eftsecure_Payment/js/view/payment/method-renderer/eftpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);