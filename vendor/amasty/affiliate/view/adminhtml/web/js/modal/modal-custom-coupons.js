define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/modal/modal-component',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function ($, _, utils, registry, modalComponent, urlBuilder, alert) {
    'use strict';

    return modalComponent.extend({
        defaults: {
            affiliateCouponId: null,
            commitUrl: '',
            modules: {
                customCouponGrid: '${ $.parentName }'
            }
        },

        initObservable: function () {
            this._super().observe('affiliateCouponId');

            return this;
        },

        setAddedCoupons: function () {
            var dynamicRows = registry.get(this.options.dynamicRows),
                dataRows = dynamicRows.recordData();

            $.ajax({
                url: urlBuilder.build(this.options.ajaxUrl),
                type: 'POST',
                dataType: 'json',
                data: {
                    'data': dataRows,
                    'rowId': this.affiliateCouponId,
                    'form_key': window.FORM_KEY
                }
            }).done(function (response) {
                if (response.status === 'done') {
                    this.customCouponGrid().source.reload();
                    this.closeModalCustomCoupons();
                }

                if (response.status === 'error') {
                    alert({
                        title: $.mage.__('Error'),
                        content: $.mage.__(response.message),
                        actions: {
                            always: function () {}
                        }
                    });
                }
            }.bind(this));
        },

        showModalCustomCoupons: function (index, affiliateCouponId) {
            var form = registry.get(this.options.renderElement);

            this.affiliateCouponId(affiliateCouponId);
            form.render();

            return this.openModal();
        },

        closeModalCustomCoupons: function () {
            var form = registry.get(this.options.renderElement);

            form.destroyInserted();

            return this.closeModal();
        }
    });
});
