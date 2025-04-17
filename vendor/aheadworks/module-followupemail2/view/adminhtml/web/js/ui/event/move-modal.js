define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal-component',
    'Magento_Ui/js/modal/confirm',
], function ($, $t, ModalComponent, confirm) {
    'use strict';

    return ModalComponent.extend({
        defaults: {
            eventId: null,
            applyCallback: null,
            confirmationMessage: ''
        },

        /**
         * Apply Done action
         *
         * @param {boolean} redirect
         */
        actionDone: function (redirect) {
            var campaignId = this.source.data.campaign_id,
                campaignDisabled = this.source.data.campaign_statuses[campaignId];

            if (this.hasChanged()) {
                this.valid = true;
                this.elems().forEach(this.validate, this);

                if (this.valid && this.applyCallback !== null) {
                    if (campaignDisabled) {
                        confirm({
                            title: $t('Information'),
                            content: this.confirmationMessage,
                            actions: {
                                confirm: function(){
                                    this.applyCallback(this.eventId, campaignId, redirect);
                                    this.closeModal();
                                }.bind(this),
                                cancel: function(){
                                    this.elems().forEach(this.setPrevValues, this);
                                    this.closeModal();
                                }.bind(this),
                                always: function(){}
                            }
                        });
                    } else {
                        this.applyCallback(this.eventId, campaignId, redirect);
                        this.closeModal();
                    }
                }
            }
        },

        /**
         * Check if modal elements has changed
         *
         * @returns {boolean}
         */
        hasChanged: function() {
            var hasChanged = false;

            this.elems().forEach(function (elem) {
                if (elem.hasChanged()) {
                    hasChanged = true;
                }
            }, this);

            return hasChanged;
        },

        /**
         * Add redirect to Done action
         */
        actionDoneAndGo: function () {
            return this.actionDone(true);
        }
    });
});
