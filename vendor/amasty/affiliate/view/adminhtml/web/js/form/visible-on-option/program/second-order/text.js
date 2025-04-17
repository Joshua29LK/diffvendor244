define([
    'Magento_Ui/js/form/element/abstract',
    'Amasty_Affiliate/js/form/visible-on-option/program/second-order/strategy'
], function (Element, strategy) {
    'use strict';

    return Element.extend(strategy);
});
