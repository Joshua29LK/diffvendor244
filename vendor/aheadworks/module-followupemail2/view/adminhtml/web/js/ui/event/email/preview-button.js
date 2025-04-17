define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function (Button, $, spinner, modal, alert) {
    'use strict';

    return Button.extend({
        defaults: {
            popupSelector: '#aw-followupemail2-preview'
        },

        /**
         * @inheritdoc
         */
        applyAction: function (action) {
            var previewUrl = action.url;
            var contentId = action.params.content;
            if (contentId) {
                contentId--;
            } else {
                contentId = 0;
            }
            var content = this.source.get('data.content')[contentId];
            var self = this;

            var emailData = {
                event_id: this.source.get('data.event_id'),
                content: content,
            };

            spinner.show();

            $.ajax({
                url: previewUrl,
                type: "POST",
                dataType: 'json',
                data: {
                    email_data: emailData
                },
                success: function(response) {
                    if (response.ajaxExpired) {
                        window.location.href = response.ajaxRedirect;
                    }
                    spinner.hide();

                    if (!response.error) {
                        var options = {
                            autoOpen: true,
                            responsive: true,
                            clickableOverlay: false,
                            innerScroll: true,
                            modalClass: 'email-preview-modal',
                            title: $.mage.__('Email Preview'),
                            buttons: [{
                                text: $.mage.__('Ok'),
                                class: '',
                            }]
                        };
                        $(self.popupSelector).remove();
                        var popupContent = $(response.preview).hide();
                        $('body').append(popupContent);

                        modal(options, $(self.popupSelector));
                        return true;
                    }
                    self.onError(response.message);
                    return false;

                }
            });
        },

        /**
         * Ajax request error handler
         *
         * @param errorMessage
         */
        onError: function (errorMessage) {
            alert({
                content: $.mage.__(errorMessage),
            });
        },

        /**
         * Hide element
         *
         * @returns {Abstract} Chainable
         */
        hide: function () {
            this.visible(false);

            return this;
        },

        /**
         * Show element
         *
         * @returns {Abstract} Chainable
         */
        show: function () {
            this.visible(true);

            return this;
        },
    });
});
