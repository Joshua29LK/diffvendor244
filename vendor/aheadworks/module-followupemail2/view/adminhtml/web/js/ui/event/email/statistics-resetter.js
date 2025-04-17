define([
    'jquery',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function($, spinner, alert, confirm){
    'use strict';

    $.awFueEmailStatResetter = {
        options: {
            url: '/',
            statSelector: '.email-content-statistics',
            emailId: null
        },

        /**
         * Initialize widget
         */
        init: function (config) {
            this.options.url = config.url;
            this.options.statSelector = config.statSelector;
            this.options.emailId = config.emailId;

            var me = this;
            $(this.options.statSelector).each(function(index, selector) {
                $(selector).on('click', me.onClickHandler.bind(me));
            });
        },

        /**
         * On click handler
         *
         * @param {Object} event
         */
        onClickHandler: function (event) {
            console.log('onClickHandler');
            var el = $(event.target),
                elementId = el.attr('id');

            var contentId = elementId.replace('aw-fue2-content-', '');

            if (contentId == 'all') {
                this.confirm(this.reset.bind(this, null));
            } else {
                this.confirm(this.reset.bind(this, contentId));
            }
        },

        /**
         * Reset content
         *
         * @param {number}|null contentId
         */
        reset: function (contentId) {
            if (this.options.emailId) {
                spinner.show();
                $.ajax({
                    url: this.options.url,
                    type: "POST",
                    dataType: 'json',
                    data: {
                        email_id: this.options.emailId,
                        content_id: contentId
                    },
                    success: function(response) {
                        if (response.ajaxExpired) {
                            window.location.href = response.ajaxRedirect;
                        }

                        if (!response.error) {
                            window.location.href = response.redirect_url;
                            return true;
                        }

                        spinner.hide();
                        self.onError(response.message);
                        return false;
                    }
                });
            }
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
         * Display confirm message before applying callback
         *
         * @param {Function} callback
         */
        confirm: function (callback) {
            confirm({
                title: $.mage.__('Are you sure you want to reset the email statistics?'),
                content: $.mage.__('The email statistics will be set to 0. This action cannot be reversed.'),
                actions: {
                    confirm: callback
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $.mage.__('Reset Statistics'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },
    }

    return $.awFueEmailStatResetter;
});
