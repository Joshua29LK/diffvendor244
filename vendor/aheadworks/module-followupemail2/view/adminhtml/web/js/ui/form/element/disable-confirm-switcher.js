define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function (Checkbox, confirm, $t) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            displayConfirm: true,
            confirmMessage: '',
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            if (parseInt(this.source.data.status) == 1) {
                this.displayConfirm = true;
            } else {
                this.displayConfirm = false;
            }
            return this;
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            if (!newChecked && this.displayConfirm) {
                confirm({
                    title: $t('Information'),
                    content: this.confirmMessage,
                    actions: {
                        confirm: function(){
                            this.displayConfirm = false;
                            this.onCheckedChanged(false);
                        }.bind(this),
                        cancel: function(){
                            this.onCheckedChanged(true);
                        }.bind(this),
                        always: function(){}
                    }
                });
            } else {
                this.checked(newChecked);
                this._super(newChecked);
            }
        },
    });
});
