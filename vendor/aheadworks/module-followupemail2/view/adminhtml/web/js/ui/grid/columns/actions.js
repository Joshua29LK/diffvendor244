define([
    'Magento_Ui/js/grid/columns/actions',
], function (Actions) {
    'use strict';

    return Actions.extend({
        /**
         * @inheritdoc
         */
        applyAction: function (actionIndex, rowIndex) {
            if (actionIndex == 'preview') {
                var action = this.getAction(rowIndex, actionIndex);
                window.open(action.href, '_blank', 'resizable, scrollbars, status, top=0, left=0, width=600, height=500');
            }
            return this._super(actionIndex, rowIndex);
        },
    });
});
