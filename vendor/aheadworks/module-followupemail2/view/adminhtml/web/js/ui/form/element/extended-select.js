define([
    'Magento_Ui/js/form/element/select',
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            elementTmpl: 'Aheadworks_Followupemail2/ui/form/element/extended-select',
            size: 5
        },
    })
});
