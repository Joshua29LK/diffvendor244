define([
    'jquery',
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/spinner',
    'uiLayout'
], function ($, _, utils, $t, DynamicRows, confirm, alert, loader, layout) {
    'use strict';

    return DynamicRows.extend({
        defaults: {
            manageFormName: '${ $.ns }.${ $.ns }',
            parentRowsName: '${ $.ns }.${ $.ns }.data.events',
            emailFormName: '${ $.ns }.${ $.ns }.email_edit_modal.aw_followupemail2_email_form',
            emailFormModalName: '${ $.ns }.${ $.ns }.email_edit_modal',
            deleteProperty: false,
            dndPositionChanging: false,
            parentDndConfig: {
                name: '${ $.name }_parentDnd',
                component: 'Magento_Ui/js/dynamic-rows/dnd',
            },
            imports: {
                dndPositionChange: '${ $.dndConfig.name }:positionChange',
            },
            listens: {
                dndPositionChange: 'dndPositionChange',
            },
            modules: {
                parentRows: '${ $.parentRowsName }',
                emailForm: '${ $.emailFormName }',
                emailFormModal: '${ $.emailFormModalName }'
            }
        },

        /**
         * @inheritdoc
         */
        initHeader: function () {
            var data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);

                    cell.config.labelVisible = false;
                    _.extend(data, {
                        label: cell.config.label,
                        name: cell.name,
                        additionalClasses: cell.config.columnsHeaderClasses
                    });

                    this.labels.push(data);
                }, this);
            }
        },

        /**
         * @inheritdoc
         */
        initDnd: function () {
            if (this.dndConfig.enabled) {
                // workaround start, see M2FUE-219
                layout([this.parentDndConfig]);
                // workaround end, see M2FUE-219
                layout([this.dndConfig]);
            }

            return this;
        },

        /**
         * Get dnd component
         *
         * @returns {Object}
         */
        getDnd: function () {
            return this.dnd();
        },

        /**
         * @inheritdoc
         */
        destroy: function() {
            this.dnd().destroy();
            return this._super();
        },

        /**
         * If has record data
         *
         * @returns {boolean}
         */
        hasData: function () {
            if (this.recordData().length > 0) {
                return true;
            }
            return false;
        },

        /**
         * Get readme text
         *
         * @returns {string}
         */
        getReadmeText: function () {
            var readmeText = $t('Click "{ButtonName}" to add new email.');
            var buttonName = '<strong>' + $t('Add Email') + '</strong>';
            readmeText = readmeText.replace('{ButtonName}', buttonName);

            return readmeText + '<br />' + this.parentRows().getUserGuideText();
        },

        /**
         * Get email name
         *
         * @param {number} emailId
         * @returns {string}
         */
        getEmailName: function (emailId) {
            var emailName = '';
            var emailFound = false;
            $.each(this.source.data.events, function (eventKey, event) {
                $.each(event.emails, function (emailKey, email) {
                    if (email.id == emailId) {
                        emailName = email.name;
                        emailFound = true;
                        return false;
                    }
                });
                if (emailFound) {
                    return false;
                }
            });
            return emailName;
        },

        /**
         * Check if a/b testing mode is enabled
         *
         * @param {number} emailId
         */
        isTestingEnabled: function (emailId) {
            var abTestingMode = false;
            var emailFound = false;
            $.each(this.source.data.events, function (eventKey, event) {
                $.each(event.emails, function (emailKey, email) {
                    if (email.id == emailId) {
                        abTestingMode = (email.ab_testing_mode == 1);
                        emailFound = true;
                        return false;
                    }
                });
                if (emailFound) {
                    return false;
                }
            });
            return abTestingMode;
        },

        /**
         * Open email edit form
         *
         * @param {number} eventId
         */
        editEmailForm: function (emailId) {
            this.emailForm().destroyInserted();
            this.emailFormModal().set('options.title', this.editEmailTitle);
            var data = {
                id: emailId,
            };
            this.emailForm().updateData(data);
            this.emailFormModal().openModal();
        },

        /**
         * Open duplicate email form
         *
         * @param {number} emailId
         * @returns {*}
         */
        duplicateEmailForm: function (emailId) {
            this.emailForm().destroyInserted();
            this.emailFormModal().set('options.title', this.newEmailTitle);
            var data = {
                id: emailId,
                duplicate: true
            };
            this.emailForm().updateData(data);
            this.emailFormModal().openModal();
        },

        /**
         * Change email status
         *
         * @param {number} emailId
         * @param {Array} params
         */
        changeStatusEmail: function (emailId, params) {
            if (params.href) {
                this.showLoader();
                makeAjaxRequest(
                    params.href,
                    {id:emailId},
                    this.onSuccessChangeStatus.bind(this),
                    this.onError.bind(this)
                );
            }
        },

        /**
         * Delete email
         *
         * @param {number} emailId
         * @param {Array} params
         */
        deleteEmail: function (emailId, params) {
            if (params.href) {
                this.showLoader();
                makeAjaxRequest(
                    params.href,
                    {id:emailId},
                    this.onSuccessDelete.bind(this, emailId),
                    this.onError.bind(this)
                );
            }
        },

        /**
         * Apply action
         *
         * @param {Object} action
         * @param {Object} index
         * @param {Object} recordId
         * @returns {*}
         */
        applyAction: function (action, recordId) {
            var emailId = this._getEmailId(recordId);
            var callback = this.getCallback(action, emailId);

            if (action.confirm) {
                this.confirm(action, callback)
            } else {
                callback()
            }
            return this;
        },

        /**
         * Get callback
         *
         * @param {Object} action
         * @param {number} emailId
         * @param {number} recordId
         * @returns {Function}
         */
        getCallback: function (action, emailId) {
            var callback = action.callback,
                params = [],
                self = this;

            if (utils.isObject(callback)) {
                if (callback.params) {
                    params = callback.params;
                }
                callback = callback.target;

                return function () {
                    self[callback](emailId, params);
                };
            }
            return function () {

            };
        },

        /**
         * Get email id by record id
         *
         * @param {number} recordId
         * @returns {number}
         * @private
         */
        _getEmailId: function (recordId) {
            var records = this.recordData();
            var emailId = 0;
            records.forEach(function (record) {
                if (record.record_id == recordId) {
                    emailId = record.id;
                }
            });
            return emailId;
        },

        /**
         * Display confirm message before applying callback
         *
         * @param {Object} action
         * @param {Function} callback
         */
        confirm: function (action, callback) {
            var confirmParams = action.confirm;
            confirm({
                title: confirmParams.title,
                content: confirmParams.message,
                actions: {
                    confirm: callback
                }
            });
        },

        /**
         * Show error popup
         *
         * @param {string} errorMessage
         */
        showError: function (errorMessage) {
            alert({
                content: $t(errorMessage),
            });
        },

        /**
         * Ajax request error handler
         *
         * @param errorMessage
         */
        onError: function (errorMessage) {
            this.showError(errorMessage);
            this.hideLoader();
            this.showSpinner(false);
        },

        /**
         * Success change status handler
         *
         * @param {Object} response
         */
        onSuccessChangeStatus: function (response) {
            var event = this._getCurrentEvent();
            if (event && 'emails' in response) {
                this._updateEventEmailsData(event.id, response.emails, []);

                if ('totals' in response) {
                    this.updateTotals(event.id, response.totals);
                }
            }
            if ('events_count' in response && 'emails_count' in response) {
                this.parentRows().updateCampaignShortStatisticsData(
                    response.events_count,
                    response.emails_count,
                    response.campaign_stats
                );
            }
            this.reload();
            this.hideLoader();
            this.showSpinner(false);
        },

        /**
         * Success delete handler
         *
         * @param {number} emailId
         * @param {Object} response
         */
        onSuccessDelete: function (emailId, response) {
            var event = this._getCurrentEvent();

            if (event) {
                this._deleteEmail(event.id, emailId);
                if ('emails' in response) {
                    this._updateEventEmailsData(event.id, response.emails, []);
                }
                if ('totals' in response) {
                    this.updateTotals(event.id, response.totals);
                }
            }

            if ('events_count' in response && 'emails_count' in response) {
                this.parentRows().updateCampaignShortStatisticsData(
                    response.events_count,
                    response.emails_count,
                    response.campaign_stats
                );
            }
            this.reload();
            this.hideLoader();
            this.showSpinner(false);
        },

        /**
         * Shows loader.
         */
        showLoader: function () {
            loader.get(this.manageFormName).show();
        },

        /**
         * Hides loader.
         */
        hideLoader: function () {
            loader.get(this.manageFormName).hide();
        },

        /**
         * Get actions
         * @param {Object} record
         * @returns {Array}
         */
        getActions: function (record) {
            var email,
                actions = [];

            email = this._getEmailByRecordId(record.recordId);
            if (email !== false) {
                this.actions.forEach(function (action) {
                    if (action.condition) {
                        if (action.condition.status == email.status) {
                            actions.push(action);
                        }
                    } else {
                        actions.push(action);
                    }
                });
            }

            return actions;
        },

        /**
         * Set classes
         *
         * @param {Object} data
         * @param {Object}|null record
         * @returns {Object} Classes
         */
        setClasses: function (data, record) {
            var classes = this._super(data);

            _.extend(classes, {
                'inactive': this.isCurrentEventInactive(),
                'email-disabled': this.isEmailDisabled(record)
            });

            return classes;
        },

        /**
         * Set classes for footer
         *
         * @returns {string}
         */
        setClassesForFooter: function () {
            var classes = '';

            if (this.isCurrentEventInactive()) {
                classes = 'inactive';
            } else {
                classes = '';
            }
            return classes;
        },

        /**
         * Check if current ecent is inactive
         *
         * @param {Object} record
         * @return {Boolean}
         */
        isCurrentEventInactive: function () {
            var event = this._getCurrentEvent();

            if (event) {
                return event.status == 0;
            }

            return false;
        },


        /**
         * Check if current email in the grid is disabled
         *
         * @param {Object} record
         * @returns {boolean}
         */
        isEmailDisabled: function (record) {
            var result = false,
                email;

            if (record) {
                email = this._getEmailByRecordId(record.recordId);
                if (email !== false  && email.is_email_disabled) {
                    result =  true;
                }
            }

            return result;
        },

        /**
         * Get current event
         *
         * @returns {Object|null}
         * @private
         */
        _getCurrentEvent: function() {
            var event = null,
                records,
                recordFound = false;

            records = this.recordData();

            $.each(records, function (key, value) {
                if (value.event_id) {
                    recordFound = value;
                    return false;
                }
            });
            if (recordFound) {
                $.each(this.source.data.events, function (key, value) {
                    if (recordFound.event_id == value.id) {
                        event = value;
                        return false;
                    }
                });
            };
            return event;
        },

        /**
         * Get email by record id
         *
         * @param recordId
         * @returns {Object|false}
         * @private
         */
        _getEmailByRecordId: function(recordId) {
            var currentRecord,
                eventIndex,
                emailIndex;

            currentRecord = this._getRecordData(recordId);
            if (currentRecord !== false) {
                eventIndex = this._getEventIndex(currentRecord.event_id);
                if (eventIndex !== false) {
                    emailIndex = this._getEmailIndex(currentRecord.id);
                    if (emailIndex !== false) {
                        return this.source.data.events[eventIndex].emails[emailIndex];
                    }
                }
            }

            return false;
        },

        /**
         * Get record data
         *
         * @param {number} recordId
         * @returns {Object}|false
         * @private
         */
        _getRecordData: function (recordId) {
            var records = this.recordData(),
                recordData = false;

            if (records) {
                $.each(records, function (key, value) {
                    if (recordId == value.record_id) {
                        recordData = value;
                    }
                });
            }
            return recordData;
        },

        /**
         * Reset event statistics
         */
        resetStatistics: function () {
            var parentRecordId = this.dataScope.split('.').pop(),
                event = this.source.data.events[parentRecordId];

            confirm({
                title: $.mage.__('Are you sure you want to reset the event statistics?'),
                content: $.mage.__('All the event emails statistics will be set to 0. This action cannot be reversed.'),
                actions: {
                    confirm: function() {
                        this.showLoader();
                        $.ajax({
                            url: this.reset_statistics_url,
                            type: "POST",
                            dataType: 'json',
                            data: {id:event.id},
                            success: function(response) {
                                if (response.ajaxExpired) {
                                    window.location.href = response.ajaxRedirect;
                                }

                                if (!response.error) {
                                    window.location.href = response.redirect_url;
                                    return true;
                                }
                                this.hideLoader();
                                self.onError(response.message);
                                return false;
                            }
                        });
                    }.bind(this)
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $.mage.__('Reset Statistics'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },

        /**
         *Get totals value
         *
         * @param {string} index
         * @returns {string}
         */
        getTotals: function (index) {
            var parentRecordId = this.dataScope.split('.').pop(),
                event = this.source.data.events[parentRecordId];

            return event.totals[index];
        },

        /**
         * Update event totals
         *
         * @param {number} eventId
         * @param {Object} totals
         */
        updateTotals: function (eventId, totals) {
            var eventIndex = this._getEventIndex(eventId);
            if (eventIndex !== false) {
                this.source.data.events[eventIndex].totals = totals;
            }
        },

        /**
         * Format percent
         *
         * @param value
         * @returns {string}
         */
        formatPercent: function (value) {
            return String(Number(value * 1).toFixed(2)) + '%'
        },

        /**
         * Dnd position change callback
         *
         * @param {int} recordId
         */
        dndPositionChange: function (recordId) {
            var records,
                positions = [],
                params,
                eventId = null;

            if (recordId !== false) {
                this.dndPositionChanging = true
            } else if (this.dndPositionChanging) {
                records = this.recordData();
                records.forEach(function(record, index) {
                    eventId = record.event_id,
                    positions[index] = {
                        'id': record.id,
                        'position': record.position
                    }
                }.bind(this));

                if (eventId != null) {
                    params = {
                        event_id: eventId,
                        positions: positions
                    };

                    this.showLoader();
                    makeAjaxRequest(
                        this.change_position_url,
                        params,
                        this.onSuccessPositionChange.bind(this, eventId),
                        this.onError.bind(this)
                    );
                }

                this.dndPositionChanging = false;
            }
        },

        /**
         * Success position change handler
         *
         * @param {number} eventId
         * @param {Object} response
         */
        onSuccessPositionChange: function (eventId, response) {
            this._updateEventEmailsData(eventId, response.emails, ['when']);

            if ('emails' in response) {
                this._updateEventEmailsData(eventId, response.emails, []);
            }
            if ('totals' in response) {
                this.updateTotals(eventId, response.totals);
            }

            if ('events_count' in response && 'emails_count' in response) {
                this.parentRows().updateCampaignShortStatisticsData(
                    response.events_count,
                    response.emails_count,
                    response.campaign_stats
                );
            }
            this.reload();
            this.hideLoader();
            this.showSpinner(false);
        },

        /**
         * Update event emails data
         *
         * @param {number} eventId
         * @param {Array} emails
         * @param {Array} fieldsList
         * @private
         */
        _updateEventEmailsData: function(eventId, emails, fieldsList) {
            var eventIndex,
                emailIndex,
                oldEmail;

            eventIndex = this._getEventIndex(eventId);

            if (eventIndex !== false) {
                emails.forEach(function (email) {
                    emailIndex = this._getEmailIndex(email.id);

                    if (emailIndex !== false) {
                        if (_.isEmpty(fieldsList)) {
                            oldEmail = this.source.data.events[eventIndex].emails[emailIndex];
                            email.record_id = oldEmail.record_id;
                            this.source.data.events[eventIndex].emails[emailIndex] = email;
                        } else {
                            oldEmail = this.source.data.events[eventIndex].emails[emailIndex];
                            fieldsList.forEach(function (field) {
                                oldEmail[field] = email[field];
                            });
                            this.source.data.events[eventIndex].emails[emailIndex] = oldEmail;
                        }
                    }
                }.bind(this));
            }
        },

        /**
         * Get event index
         * @param {number} eventId
         * @returns {number|false}
         * @private
         */
        _getEventIndex: function (eventId) {
            var eventIndex = false;

            $.each(this.source.data.events, function (index, event) {
                if (event.id == eventId) {
                    eventIndex = index;
                    return false;
                }
            });

            return eventIndex;
        },

        /**
         * Get event index
         * @param {number} eventId
         * @returns {number|false}
         * @private
         */
        _getEmailIndex: function (emailId) {
            var emailIndex = false;

            $.each(this.recordData(), function (index, email) {
                if (email.id == emailId) {
                    emailIndex = index;
                    return false;
                }
            });

            return emailIndex;
        },

        /**
         * Delete email from records
         *
         * @param {number} eventId
         * @param {number} emailId
         * @private
         */
        _deleteEmail: function(eventId, emailId) {
            var eventIndex,
                emailIndex,
                email;

            eventIndex = this._getEventIndex(eventId);
            if (eventIndex !== false) {
                emailIndex = this._getEmailIndex(emailId);
                if (emailIndex !== false) {
                    email = this.source.data.events[eventIndex].emails[emailIndex];
                    this.processingDeleteRecord(emailIndex, email.record_id);
                }
            }
        }
    })

    /**
     * Make ajax request
     *
     * @param {String} url
     * @param {Array} params
     * @param {Function} successCallback
     * @param {Function} errorCallback
     */
    function makeAjaxRequest (url, params, successCallback, errorCallback) {
        params = utils.serialize(params);
        params['form_key'] = window.FORM_KEY;
        $.ajax({
            url: url,
            data: params,
            dataType: 'json',

            /**
             * Success callback.
             * @param {Object} response
             * @returns {Boolean}
             */
            success: function (response) {
                if (response.ajaxExpired) {
                    window.location.href = response.ajaxRedirect;
                }

                if (!response.error) {
                    successCallback(response);
                    return true;
                }
                errorCallback(response.message);
                return false;
            },
        });
    }
});
