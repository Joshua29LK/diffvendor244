define(function () {
    'use strict';

    return {
        defaults: {
            valuesForOptions: [],
            imports: {
                toggleVisibility:
                    'amasty_affiliate_banner_form.amasty_affiliate_banner_form.general.type:value'
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
