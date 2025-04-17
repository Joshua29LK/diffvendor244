define([
    'ko',
    'Magento_Ui/js/form/components/fieldset'
], function (
    ko,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            withdrawalType: '',
            commissionType: ''
        },

        initObservable: function () {
            this._super().observe('withdrawalType commissionType');

            this.visible = ko.computed(function () {
                return this.withdrawalType() === 'per_sale' && this.commissionType() === 'percent';
            }.bind(this));

            return this;
        }
    });
});
