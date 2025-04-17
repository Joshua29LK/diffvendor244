define([
    'jquery',
    'underscore'
], function($, _) {
    'use strict';

    $.widget('mage.amastyLegacyFormFieldEnabler', {
        options: {
            depFields: null,
            map: {
                '1': false,
                '0': true
            }
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                change: function (event) {
                    var value = $(event.currentTarget).val();

                    if (!_.isNull(this.options.depFields)
                        && _.has(this.options.map, value)
                    ) {
                        $(this.options.depFields).prop('disabled', this.options.map[value]);
                    }
                }
            });
        }
    });

    return $.mage.amastyLegacyFormFieldEnabler;
});
