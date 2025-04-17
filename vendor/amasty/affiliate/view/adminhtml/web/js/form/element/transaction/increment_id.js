define(
    ['Magento_Ui/js/form/element/abstract','uiRegistry'],
    function(Abstract,registry) {
    return Abstract.extend({
        defaults: {
            data: registry.get('amasty_affiliate_transaction_form.amasty_affiliate_transaction_form_data_source').data,
            link: '',
            orderUrl: registry.get('amasty_affiliate_transaction_form.amasty_affiliate_transaction_form_data_source').data.order_url
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            this.link = this.data.increment_id;
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
                    'orderUrl'
                ]);
        }

    });
});
