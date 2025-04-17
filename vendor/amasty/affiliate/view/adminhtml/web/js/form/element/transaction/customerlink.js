define(
    ['Magento_Ui/js/form/element/abstract','uiRegistry'],
    function(Abstract,registry) {
    return Abstract.extend({
        defaults: {
            data: registry.get('amasty_affiliate_transaction_form.amasty_affiliate_transaction_form_data_source').data,
            link: '',
            customerUrl: registry.get('amasty_affiliate_transaction_form.amasty_affiliate_transaction_form_data_source').data.customer_url,
            customerLinkClass: registry.get('amasty_affiliate_transaction_form.amasty_affiliate_transaction_form_data_source').data.customer_link_class
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            this.link = this.data.customer_email;
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
                    'link',
                    'customerUrl',
                    'customerLinkClass'
                ]);
        }

    });
});
