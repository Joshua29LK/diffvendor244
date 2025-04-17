define([
    'jquery',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'mage/translate',
    'uiRegistry',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/spinner',
    'mageUtils'
], function ($, _, DynamicRows, $t, registry, confirm, alert, loader, utils) {
    'use strict';

    return DynamicRows.extend({
        defaults: {
            manageFormName: '${ $.ns }.${ $.ns }',
            eventFormName: '${ $.ns }.${ $.ns }.event_edit_modal.aw_followupemail2_event_form',
            eventFormModalName: '${ $.ns }.${ $.ns }.event_edit_modal',
            editEventTitle: $t('Edit Event'),
            newEventTitle: $t('New Event'),
            confirmDeleteTitle: $t('Are you sure you want to delete the "{EventName}" event?'),
            emailFormName: '${ $.ns }.${ $.ns }.email_edit_modal.aw_followupemail2_email_form',
            emailFormModalName: '${ $.ns }.${ $.ns }.email_edit_modal',
            eventMovePopupName: '${ $.ns }.${ $.ns }.event_move_modal',
            editEmailTitle: $t('Edit Email'),
            newEmailTitle: $t('New Email'),
            userGuideText: '',
            userGuideLink: '',
            deleteProperty: false,
            eventsCountSelector: "#campaign-events-count",
            emailsCountSelector: "#campaign-emails-count",
            campaignStatsSelector: ".campaign-statistics",
            imports: {
                eventResponseData: '${ $.eventFormName }:responseData',
                emailResponseData: '${ $.emailFormName }:responseData',
            },
            listens: {
                eventResponseData: 'afterEventFormUpdate',
                emailResponseData: 'afterEmailFormUpdate',
            },
            modules: {
                manageForm: '${ $.manageFormName }',
                eventForm: '${ $.eventFormName }',
                eventFormModal: '${ $.eventFormModalName }',
                emailForm: '${ $.emailFormName }',
                emailFormModal: '${ $.emailFormModalName }',
                eventMovePopup: '${ $.eventMovePopupName }'
            }
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
            var readmeText = $t('Click "{ButtonName}" to add new event.');
            var buttonName = '<strong>' + $t('Create Event') + '</strong>';
            readmeText = readmeText.replace('{ButtonName}', buttonName);

            return readmeText + '<br />' + this.getUserGuideText();
        },

        /**
         * Get user guide text
         *
         * @returns {string}
         */
        getUserGuideText: function () {
            var userGuideLink =
                '<a href="' + this.userGuideLink +'" target="_blank">' + $t('user guide') + '</a>';
            return this.userGuideText.replace('{UserGuide}', userGuideLink);
        },

        /**
         * After event form update callback
         *
         * @param {Object} response
         */
        afterEventFormUpdate: function (response) {
            if (response.ajaxExpired) {
                window.location.href = response.ajaxRedirect;
            }
            if (!response.error) {
                var event = response.event;

                if (response.create) {
                    var index = this.getMaxRecordId() + 1;
                    event.record_id = index;
                    this.source.data.events.unshift(event);
                    this.relatedData.unshift(event);
                    if ("events_count" in response && "emails_count" in response) {
                        this.updateCampaignShortStatisticsData(response.events_count, response.emails_count);
                    }
                    this.reload();
                } else {
                    var eventFound = false;
                    var eventIndex = 0;
                    this.source.data.events.forEach(function (oldEvent, index) {
                        if (oldEvent.id == event.id) {
                            eventFound = true;
                            eventIndex = index;
                        }
                    });
                    if (eventFound) {
                        event.record_id = this.source.data.events[eventIndex].record_id;
                        this.source.data.events[eventIndex] = event;
                        this.reload();
                    }
                }
                this.eventFormModal().closeModal();
                this.hideLoader();
                this.showSpinner(false);
            } else {
                this.showError(response.message);
            }
        },

        /**
         * Get max record id
         *
         * @returns {number}
         */
        getMaxRecordId: function () {
            var records = this.recordData();
            var maxRecordId = 0;
            $.each(records, function (key, value) {
                if (value.record_id > maxRecordId) {
                    maxRecordId = value.record_id;
                }
            });
            return maxRecordId;
        },

        /**
         * After email form update callback
         *
         * @param {Object} response
         */
        afterEmailFormUpdate: function (response) {
            var email;

            if (response.ajaxExpired) {
                window.location.href = response.ajaxRedirect;
            }
            if (!response.error) {
                if (response.send_test) {
                    this.showInformation(response.message);
                }

                if ('emails' in response) {
                    email = _.first(response.emails);
                    if (email && 'totals' in response) {
                        this._updateEventEmailsData(email.event_id, response.emails);
                        this._updateEventTotals(email.event_id, response.totals);
                        this.reload();
                    }
                }

                if ("events_count" in response && "emails_count" in response) {
                    if ("campaign_stats" in response) {
                        this.updateCampaignShortStatisticsData(
                            response.events_count,
                            response.emails_count,
                            response.campaign_stats
                        );
                    }  else {
                        this.updateCampaignShortStatisticsData(response.events_count, response.emails_count);
                    }
                }
                if (!response.continue_edit) {
                    this.emailFormModal().closeModal();
                } else {
                    if (response.create) {
                        return this.continueEditEmailForm(response.continue_edit);
                    }
                }
                this.hideLoader();
                this.showSpinner(false);
            } else {
                this.showError(response.message);
            }
        },

        /**
         * Update event emails data
         *
         * @param {number} eventId
         * @param {Array} emails
         * @param {Array} fieldsList
         * @private
         */
        _updateEventEmailsData: function(eventId, emails) {
            var eventIndex,
                emailIndex,
                newEmailIndex;

            eventIndex = this._getEventIndex(eventId);

            if (eventIndex !== false) {
                emails.forEach(function (email) {
                    emailIndex = this._getEmailIndex(eventId, email.id);
                    if (emailIndex !== false) {
                        email.record_id = this.source.data.events[eventIndex].emails[emailIndex].record_id;
                        this.source.data.events[eventIndex].emails[emailIndex] = email;
                    } else {
                        newEmailIndex = this.source.data.events[eventIndex].emails.length;
                        email.record_id = newEmailIndex;
                        this.source.data.events[eventIndex].emails.push(email);
                    }
                }.bind(this));
            }
        },

        /**
         * Update event totals
         *
         * @param {number} eventId
         * @param {Object} totals
         * @private
         */
        _updateEventTotals: function (eventId, totals) {
            var eventIndex = this._getEventIndex(eventId);
            if (eventIndex !== false) {
                this.source.data.events[eventIndex].totals = totals;
            }
        },

        /**
         * Open create event form
         */
        createEventForm: function (params, values) {
            this.eventForm().destroyInserted();
            this.eventFormModal().set('options.title', this.newEventTitle);
            var data = {campaign_id: this.source.data.campaign.id};
            if (params) {
                var eventType = values.event_type;
                data.event_type = eventType;
            }
            this.eventForm().updateData(data);
            this.eventFormModal().openModal();
        },

        /**
         * Open event edit form
         *
         * @param {int} eventId
         */
        editEventForm: function (eventId) {
            this.eventForm().destroyInserted();
            this.eventFormModal().set('options.title', this.editEventTitle);
            var data = {
                id: eventId,
            };
            this.eventForm().updateData(data);
            this.eventFormModal().openModal();
        },

        /**
         * Open duplicate event form
         *
         * @param {int} eventId
         */
        duplicateEvent: function (eventId) {
            this.eventForm().destroyInserted();
            this.eventFormModal().set('options.title', this.newEventTitle);
            var data = {
                id: eventId,
                duplicate: true
            };
            this.eventForm().updateData(data);
            this.eventFormModal().openModal();
        },

        /**
         * Open move event popup
         *
         * @param {number} eventId
         */
        moveEvent: function (eventId) {
            this.eventMovePopup().eventId = eventId;
            this.eventMovePopup().applyCallback = this.moveEventCallback.bind(this);
            this.eventMovePopup().openModal();
        },

        /**
         * Move event callback
         *
         * @param {number} eventId
         * @param {string} campaignId
         * @param {boolean} redirect
         */
        moveEventCallback: function (eventId, campaignId, redirect) {
            var params = {
                event_id: eventId,
                campaign_id: campaignId,
                redirect: redirect
            };
            this.showLoader();
            $.ajax({
                url: this.source.move_event_url,
                data: params,
                dataType: 'json',
                success: function (response) {
                    if (response.ajaxExpired) {
                        window.location.href = response.ajaxRedirect;
                    }
                    if (!response.error) {
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            window.location.reload();
                        }
                        return true;
                    }
                    this.onError(response.message);
                    return false;
                }.bind(this),
            });
        },

        /**
         * Delete event
         *
         * @param {int} eventId
         */
        deleteEvent: function (eventId) {
            var eventName = '';
            this.source.data.events.forEach(function (event) {
                if (event.id == eventId) {
                    eventName = event.name;
                }
            });
            confirm({
                title: this.confirmDeleteTitle.replace('{EventName}', eventName),
                content: this.confirmDeleteContent,
                actions: {
                    confirm: function(){
                        this.showLoader();
                        makeAjaxRequest(
                            this.source.delete_event_url,
                            {id:eventId},
                            this.onSuccessDelete.bind(this, eventId),
                            this.onError.bind(this)
                        );
                    }.bind(this),
                    cancel: function(){
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
                    text: $t('Delete Event'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },

        /**
         * Open create email form
         *
         * @param {int} eventId
         */
        createEmailForm: function (eventId) {
            this.emailForm().destroyInserted();
            this.emailFormModal().set('options.title', this.newEmailTitle);
            var data = {
                event_id: eventId,
            };
            this.emailForm().updateData(data);
            this.emailFormModal().openModal();
        },

        onCreateEventButtonClick: function () {
            $('.actions-split')
                .on('click.splitDefault', '.action-default', function(event) {
                    $(this).siblings('.action-toggle').trigger('click');
                    event.stopImmediatePropagation();
                });
        },

        /**
         * Reset email form
         */
        resetEmailForm: function () {
            this.emailForm().resetForm();
        },

        /**
         * Open email form to continue edit
         *
         * @param {int} eventId
         */
        continueEditEmailForm: function (emailId) {
            this.emailForm().destroyInserted();
            this.emailFormModal().set('options.title', this.editEmailTitle);
            var data = {
                id: emailId,
            };
            this.emailForm().updateData(data);
            this.emailFormModal().openModal();
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
         * Show message popup
         *
         * @param {string} message
         */
        showInformation: function (message) {
            alert({
                title: $t('Information'),
                content: $t(message),
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
         * Success delete handler
         *
         * @param {int} eventId
         */
        onSuccessDelete: function (eventId, eventsCount, emailsCount, campaignStats) {
            var events = this.source.data.events;
            var eventIndex = 0;
            var recordId = 0;
            var eventFound = false;
            events.forEach(function (event, index) {
                if (event.id == eventId) {
                    eventFound = true;
                    eventIndex = index;
                    recordId = event.record_id
                }
            });
            if (eventFound) {
                this.processingDeleteRecord(eventIndex, recordId);
                if (eventsCount != null  && emailsCount != null) {
                    this.updateCampaignShortStatisticsData(eventsCount, emailsCount, campaignStats);
                }
                this.reload();
            }
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
         * Update campaign short statistics data
         *
         * @param int eventsCount
         * @param int emailsCount
         * @param {Object} campaignStats
         * @returns {*}
         */
        updateCampaignShortStatisticsData: function (eventsCount, emailsCount, campaignStats) {
            $(this.eventsCountSelector).html(eventsCount);
            $(this.emailsCountSelector).html(emailsCount);

            if (campaignStats) {
                $(this.campaignStatsSelector + ' .sent').html(campaignStats.sent);
                $(this.campaignStatsSelector + ' .opened').html(campaignStats.opened);
                $(this.campaignStatsSelector + ' .clicks').html(campaignStats.clicked);
                $(this.campaignStatsSelector + ' .open-rate').html(this.toPercent(campaignStats.open_rate));
                $(this.campaignStatsSelector + ' .click-rate').html(this.toPercent(campaignStats.click_rate));
            }

            return this;
        },

        /**
         * To percent
         *
         * @param {Number} value
         * @return {String} value
         */
        toPercent: function (value) {
            return String(Number(value).toFixed(2)) + '%';
        },

        /**
         * Is inactive
         *
         * @param {Object} record
         * @return {Boolean}
         */
        isInactive: function (record) {
            var recordData = this.getCurrentRecordData(record);
            if (recordData) {
                return recordData.status == 0;
            };
            return false;
        },

        /**
         * Get event type
         *
         * @param record
         * @returns {string}
         */
        getEventTypeLabel: function (record) {
            var recordData = this.getCurrentRecordData(record);
            if (recordData) {
                return $t(recordData.event_type_label);
            };
            return '';
        },

        /**
         * Get current record data
         *
         * @param {Object} record
         * @returns {Object}|false
         */
        getCurrentRecordData: function (record) {
            var records = this.recordData();
            var recordFound = false;
            $.each(records, function (key, value) {
                if (record.recordId == value.record_id) {
                    recordFound = value;
                    return false;
                }
            });
            if (recordFound) {
                return recordFound;
            };
            return false;
        },

        /**
         * Get event index
         *
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
         * @param {number} emailId
         * @returns {number|false}
         * @private
         */
        _getEmailIndex: function (eventId, emailId) {
            var emailIndex = false,
                eventIndex;

            eventIndex = this._getEventIndex(eventId);
            if (eventIndex !== false) {
                $.each(this.source.data.events[eventIndex].emails, function (index, email) {
                    if (email.id == emailId) {
                        emailIndex = index;
                        return false;
                    }
                });
            }

            return emailIndex;
        }
    });

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
                    var eventsCount = null;
                    var emailsCount = null;
                    var campaignStats = null;
                    if ("events_count" in response && "emails_count" in response) {
                        eventsCount = response.events_count;
                        emailsCount = response.emails_count;
                    }
                    if ("campaign_stats" in response) {
                        campaignStats = response.campaign_stats;
                    }

                    successCallback(eventsCount, emailsCount, campaignStats);
                    return true;
                }
                errorCallback(response.message);
                return false;
            },
        });
    }
});
