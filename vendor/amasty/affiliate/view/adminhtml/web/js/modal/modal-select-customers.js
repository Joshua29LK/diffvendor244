define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/modal/modal-component'
], function ($, _, utils, registry, modalComponent) {
    'use strict';

    return modalComponent.extend({
        setSelectedCustomers: function () {
            var field = registry.get(this.targetField),
                grid = this.getChild('customer_grid'),
                provider = grid.selections(),
                selected = provider.selected();
            field.value(selected.join(','));
        }
    })
});
