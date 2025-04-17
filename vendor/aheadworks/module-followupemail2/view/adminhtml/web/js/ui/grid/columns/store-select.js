define([
    'underscore',
    'Magento_Ui/js/grid/columns/column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        /**
         * Get column label
         *
         * @returns {String}
         */
        getLabel: function () {
            var options = this.options || [],
                value =  String(this._super()),
                label = '';

            options = this._getFlatOptions(options);

            options.forEach(function (option) {
                if (option.value == value) {
                    label = option.label;
                }
            });

            return label;
        },

         /**
         * Convert tree store options to flat options array
         *
         * @param {Array} options
         * @returns {Array}
         * @private
         */
        _getFlatOptions: function (options) {
            var self = this;

            return options.reduce(function (flatOptions, option) {
                if (_.isArray(option.value)) {
                    flatOptions = flatOptions.concat(self._getFlatOptions(option.value));
                } else {
                    flatOptions.push(option);
                }

                return flatOptions;
            }, []);
        }
    });
});
