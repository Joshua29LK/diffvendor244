define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/grid/data-storage'
], function ($, _, utils, reg, Class) {
    'use strict';

    return Class.extend({
        requestData: function (params) {
            var query = utils.copy(params),
                handler = this.onRequestComplete.bind(this, query),
                request;
            reg.async('amasty_affiliate_account_form.amasty_affiliate_account_form.general.account_id')(function (provide) {
                query['account_id'] = provide.value();
            });
            this.requestConfig.data = query;
            request = $.ajax(this.requestConfig).done(handler);

            return request;
        }
    });
});
