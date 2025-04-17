define(
    ['jquery', 'Magento_Ui/js/form/element/select','uiRegistry'],
    function($, Abstract,registry) {
    return Abstract.extend({
        defaults: {
            cart_price_url: registry.get('amasty_affiliate_program_form.amasty_affiliate_program_form_data_source').cart_price_url,
            new_cart_price_url: registry.get('amasty_affiliate_program_form.amasty_affiliate_program_form_data_source').new_cart_price_url
        },

        initialize: function () {
            this._super();

            return this;
        },

        /**
         * Init observables
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'ruleUrl'
                ]);
        },
        
        openRule: function () {
            var ruleId = $('#program_cart_price_rule').val();
            var resUrl = this.cart_price_url;
            if (ruleId > 0) {
                resUrl = this.cart_price_url.replace("promo_quote/edit/", "promo_quote/edit/id/" + ruleId + '/');
            }
            window.open(resUrl, '_blank')
        },

        openNewRule: function () {
            window.open(this.new_cart_price_url, '_blank')
        }
    });
});
