define(['Magento_Ui/js/form/element/abstract','uiRegistry'],function(Abstract,registry) {
    var data = registry.get('amasty_affiliate_account_form.amasty_affiliate_account_form_data_source').data;
    return Abstract.extend({
        defaults: {
            balance: data['balance']
        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            return this._super();
        },


        /**
         * Init observables
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'balance'
                ]);
        }
    });
});
