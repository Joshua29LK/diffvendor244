define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend({
        /**
         * Disable element.
         *
         * @returns {Abstract} Chainable.
         */
        disable: function () {
            this.disabled = true;
            this.elems().forEach(function (elem) {
                try {
                    elem.disabled(true);
                }
                catch (e) {

                }
            });

            return this;
        },

        /**
         * Enable element.
         *
         * @returns {Abstract} Chainable.
         */
        enable: function () {
            this.disabled = false;
            this.elems().forEach(function (elem) {
                try {
                    elem.disabled(false);
                }
                catch (e) {

                }
            });

            return this;
        },
    });
});
