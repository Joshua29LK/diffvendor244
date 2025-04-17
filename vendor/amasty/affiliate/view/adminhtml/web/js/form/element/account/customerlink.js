define(
    ['Magento_Ui/js/form/element/abstract','uiRegistry'],
    function(Abstract,registry) {
    return Abstract.extend({
        defaults: {
            data: registry.get('amasty_affiliate_account_form.amasty_affiliate_account_form_data_source').data,
            link: '',
            customerUrl: registry.get('amasty_affiliate_account_form.amasty_affiliate_account_form_data_source').data.customer_url
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            this.link = this.data.firstname + ' ' + this.data.lastname;
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
                    'customerUrl'
                ]);
        }

    });
});
