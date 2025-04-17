define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form'
], function ($, _, Form) {
    'use strict';

    return Form.extend({
        defaults: {
            listens: {
                responseData: 'updateContentData'
            }
        },

        /**
         * Validate and save form with sendtest parameter (content A).
         */
        sendtestA: function () {
            var data = {
                "sendtest": 1,
                "content_id": 1
            };

            this.save(false, data);
        },

        /**
         * Validate and save form with sendtest parameter (content B).
         */
        sendtestB: function () {
            var data = {
                "sendtest": 1,
                "content_id": 2
            };

            this.save(false, data);
        },

        /**
         * Submits form
         *
         * @param {String} redirect
         */
        submit: function (redirect) {
            this._super(redirect);
            this.source.set('data.sendtest', 0);
        },

        /**
         * Update content data
         *
         * @param {Object} responseData
         * @return {Form}
         */
        updateContentData: function (responseData) {
            var emailId = this.source.data.id,
                emails = _.has(responseData, 'emails') ? responseData.emails : [],
                self = this;

            if (emailId && !_.isEmpty(emails)) {
                _.each(emails, function (emailData) {
                    if (emailData.id == emailId) {
                        self.source.data.content = emailData.content;
                    }
                });
            }

            return this;
        }
    })
});