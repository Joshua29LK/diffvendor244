define(function () {
    'use strict';

    return {
        defaults: {
            valuesForOptions: [],
            imports: {
                toggleVisibility:
                    'amasty_affiliate_program_form.amasty_affiliate_program_form.commission.withdrawal_type:value'
            },
            isShown: false,
            inverseVisibility: false
        },

        toggleVisibility: function (selected) {
            this.isShown = selected in this.valuesForOptions;
            this.visible(this.inverseVisibility ? !this.isShown : this.isShown);
        }
    };
});
