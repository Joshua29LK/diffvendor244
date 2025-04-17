define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'uiRegistry'
], function ($, Checkbox, confirm, $t, registry) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            displayConfirm: true,
            version: null,
            statisticsSelector: '.email-content-statistics',
            exports: {
                version: 'ns = ${ $.ns }, index = primary_email_content :value',
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.applySwitcher();
            this.version(this.source.data.primary_email_content);
            if (parseInt(this.source.data.ab_testing_mode) == 1) {
                this.displayConfirm = true;
            } else {
                this.displayConfirm = false;
            }
            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this
                ._super()
                .observe('version');
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            if (!newChecked && this.displayConfirm) {
                var self = this,
                    statisticsContent = $(this.statisticsSelector).parent().html(),
                    content = $(statisticsContent).find('.statistics-reset').html('').end()[0].outerHTML;

                confirm({
                    title: $t('You are about to disable A/B Testing. Decide which version you want to set as primary.'),
                    content: content,
                    actions: {
                        confirm: function(){
                            this.displayConfirm = false;
                            this.onCheckedChanged(false);
                        }.bind(this),
                        cancel: function(){
                            this.onCheckedChanged(true);
                        }.bind(this),
                        always: function(){}
                    },
                    buttons: [{
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $t('Version A'),
                        class: 'action-primary action-accept',
                        click: function (event) {
                            self.version(null);
                            self.version(1);
                            this.closeModal(event, true);
                        }
                    }, {
                        text: $t('Version B'),
                        class: 'action-primary action-accept',
                        click: function (event) {
                            self.version(null);
                            self.version(2);
                            this.closeModal(event, true);
                        }
                    }]
                });
            } else {
                this.checked(newChecked);
                this._super(newChecked);
            }
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            this._super();
            this.applySwitcher();
            this.version(null);
            this.version(this.source.data.primary_email_content);
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
