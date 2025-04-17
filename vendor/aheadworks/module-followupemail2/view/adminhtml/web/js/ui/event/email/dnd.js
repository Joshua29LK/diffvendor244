define([
    'ko',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dnd'
], function (ko, _, Dnd) {
    'use strict';

    /**
     * Get element context
     */
    function getContext(elem) {
        return ko.contextFor(elem);
    }

    return Dnd.extend({
        defaults: {
            positionChange: false
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'positionChange'
                ]);
        },

        /**
         * @inheritdoc
         */
        setPosition: function (depElem, depElementCtx, dragData) {
            this.positionChange(depElementCtx.recordId);
            this._super(depElem, depElementCtx, dragData);
            this.positionChange(false);
        },

        /**
         * Get record context by element
         *
         * @param {Object} elem - original element
         * @returns {Object} draggable record context
         */
        getRecord: function (elem) {
            var ctx = getContext(elem),
                index = _.isFunction(ctx.$index) ? ctx.$index() : ctx.$index,
                recordCtx = this.recordsCache()[index];

            if (_.isEmpty(recordCtx)) {
                this.setCacheRecords(this.parentComponent().elems());
                recordCtx = this.recordsCache()[index];
            }

            return recordCtx;
        }
    })
});
