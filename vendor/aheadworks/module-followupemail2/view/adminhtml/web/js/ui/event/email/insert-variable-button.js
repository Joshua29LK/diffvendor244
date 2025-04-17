define([
    'Magento_Ui/js/form/components/button'
], function (Button) {
    'use strict';

    return Button.extend({
        defaults: {
            contentSelector: null,
            variablesJsSource: 'Magento_Variable/variables'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super();

            if (this.variablesJsSource.length) {
                require([this.variablesJsSource]);
            }

            return this;
        },

        /**
         * Open insert variable window
         */
        insertVariable: function () {
            if (this.source.data.variables && Variables) {
                Variables.resetData();
                Variables.init(this.contentSelector);
                Variables.openVariableChooser(this.source.data.variables);
            }
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
        }
    });
});
