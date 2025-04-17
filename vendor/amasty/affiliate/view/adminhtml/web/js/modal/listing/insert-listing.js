define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/components/insert-listing'
], function ($, _, utils, registry, insertListing) {
    'use strict';

    return insertListing.extend({
        initialUpdateListing: function () {
            this._super();
            this.setInitialValue();
        },

        setInitialValue: function () {
            var field = registry.get(this.targetField),
                splittedString = [];

            if (field.value()) {
                splittedString = field.value().split(',');
            }

            splittedString.forEach(function (id) {
                this.selections().select(id);
            }, this);
        }
    });
});
