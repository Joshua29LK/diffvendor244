/**
 * Change Status Label for transactions that subtracted commission
 */
define([
    'Magento_Ui/js/grid/columns/select',
], function (SelectColumn) {

    return SelectColumn.extend({
        defaults: {
            typeSubtractionValue: '1',
            typeSubtractionLabel: ''
        },

        /**
         * Change Label for transactions that subtracted commission
         * @param record
         * @returns {String|Function}
         */
        getLabel: function (record) {
            if (record.balance_change_type === this.typeSubtractionValue && record.type !== 'withdrawal') {
                return  this.typeSubtractionLabel;
            }

            return this._super()
        }
    });
});
