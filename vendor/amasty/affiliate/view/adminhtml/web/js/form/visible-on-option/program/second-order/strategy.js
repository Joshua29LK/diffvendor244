define(function () {
    'use strict';

    return {
        defaults: {
            valuesForOptions: [],
            secondValuesForOptions: [],
            imports: {
                toggleVisibility:
                    'amasty_affiliate_program_form.amasty_affiliate_program_form.commission.from_second_order:value',
                secondToggleVisibility:
                    'amasty_affiliate_program_form.amasty_affiliate_program_form.commission.withdrawal_type:value'
            },
            isShown: false,
            isSecondShown: false
        },

        toggleVisibility: function (selected) {
            this.isShown = selected in this.valuesForOptions;
            this.visible(this.isShown && this.isSecondShown);
        },

        secondToggleVisibility: function (selected) {
            this.isSecondShown = selected in this.secondValuesForOptions;
            this.visible(this.isShown && this.isSecondShown);
        }
    };
});
