define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            if (this.visible()) {
                this.applySwitcher();
            }
            return this;
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            this._super();
            if (this.visible()) {
                this.applySwitcher();
            }
        },

        /**
         * Apply switcher rules (workaround due to native switcher do not work on popup form reopen)
         */
        applySwitcher: function() {
            this.switcherConfig.rules.forEach(this.applySwitcherRule, this);
        },

        /**
         * Apply switcher rule
         *
         * @param {Object} rule
         */
        applySwitcherRule: function(rule) {
            var value = this.value();
            var actions = rule.actions;

            if (rule.value != value) {
                return;
            }
            actions.forEach(this.applySwitcherAction, this);
        },

        /**
         * Apply switcher action
         *
         * @param {Object} action
         */
        applySwitcherAction: function (action) {
            registry.get(action.target, function (target) {
                var callback = target[action.callback];

                callback.apply(target, action.params || []);
            });
        },
    });
});
