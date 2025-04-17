define(
    ['Magento_Ui/js/form/element/abstract','uiRegistry'],
    function(Abstract,registry) {
    return Abstract.extend({
        defaults: {
            data: registry.get('amasty_affiliate_withdrawal_form.amasty_affiliate_withdrawal_form_data_source').data,
            link: '',
            accountUrl: registry.get('amasty_affiliate_withdrawal_form.amasty_affiliate_withdrawal_form_data_source').data.affiliate_url
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            this.link = this.data.email;
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
                    'accountUrl'
                ]);
        }

    });
});
