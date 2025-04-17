define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiElement',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function ($, _, utils, uiElement, loader, modal, alert) {
    'use strict';

    return uiElement.extend({
        defaults: {
            popupSelector: '#aw-followupemail2-preview',
            modalClass: 'email-preview-modal',
            modalHeader: 'Email Preview',
            spinner: '',
            preview_url: '',
            send_url: '',
            imports: {
                rowsData: '${ $.provider }:data.items',
            }
        },

        /**
         * Show preview by event queue id
         *
         * @param {number} id
         */
        preview: function(id) {
            var row = this.getRow(id),
                params;

            if (row && 'event_queue_id' in row) {
                params = {
                    id:row.event_queue_id
                };
                this.showLoader();
                makeAjaxRequest(
                    this.preview_url,
                    params,
                    this.onSuccess.bind(this),
                    this.onError.bind(this)
                );
            }
        },

        /**
         * Ajax request error handler
         *
         * @param errorMessage
         */
        onError: function (errorMessage) {
            this.showError(errorMessage);
            this.hideLoader();
        },

        /**
         * Ajax request success handler
         *
         * @param {Object} response
         */
        onSuccess: function (response) {
            var options,
                popupContent
                self = this;

            this.hideLoader();
            options = {
                autoOpen: true,
                responsive: true,
                clickableOverlay: true,
                innerScroll: true,
                modalClass: this.modalClass,
                title: this.modalHeader,
                buttons: [{
                    text: $.mage.__('Send Now'),
                    class: '',
                    /**
                     * Button click handler
                     */
                    click: function (event) {
                        this.closeModal(event);
                        self.send(response.id)
                    }
                }],
                 /**
                 * Modal close handler
                 */
                closed: function () {
                    $(this.popupSelector).remove();
                }.bind(this),
            };

            popupContent = $(response.preview).hide();
            $('body').append(popupContent);

            modal(options, $(this.popupSelector));
        },

        /**
         * Send email immediately
         * @param {number} id
         */
        send: function (id) {
            var params = {
                id: id
            };
            this.showLoader();
            makeAjaxRequest(
                this.send_url,
                params,
                this.onSuccessSend.bind(this),
                this.onError.bind(this)
            );
        },

        /**
         * On success send handler
         *
         * @param {Object} response
         */
        onSuccessSend: function(response) {
            window.location.href = response.redirect_url;
        },

        /**
         * Get row
         *
         * @param {number} id
         * @returns {Object}
         */
        getRow: function (id) {
            if (_.isEmpty(this.rowsData[id])) {
                return false;
            }
            return this.rowsData[id];
        },

        /**
         * Shows loader.
         */
        showLoader: function () {
            loader.get(this.spinner).show();
        },

        /**
         * Hides loader.
         */
        hideLoader: function () {
            loader.get(this.spinner).hide();
        },

        /**
         * Show error popup
         *
         * @param {string} errorMessage
         */
        showError: function (errorMessage) {
            alert({
                content: $.mage.__(errorMessage),
            });
        },
    });

    /**
     * Make ajax request
     *
     * @param {String} url
     * @param {Array} params
     * @param {Function} successCallback
     * @param {Function} errorCallback
     */
    function makeAjaxRequest (url, params, successCallback, errorCallback) {
        params = utils.serialize(params);
        params['form_key'] = window.FORM_KEY;
        $.ajax({
            url: url,
            data: params,
            dataType: 'json',

            /**
             * Success callback.
             * @param {Object} response
             * @returns {Boolean}
             */
            success: function (response) {
                if (response.ajaxExpired) {
                    window.location.href = response.ajaxRedirect;
                }

                if (!response.error) {
                    successCallback(response);
                    return true;
                }
                errorCallback(response.message);
                return false;
            },
        });
    }
});
