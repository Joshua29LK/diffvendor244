define([
    'jquery',
    'underscore',
    'mage/translate',
    'uiRegistry',
    'Magento_Ui/js/grid/listing',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mageUtils'
], function ($, _, $t, registry, Listing, confirm, alert, utils) {
    'use strict';

    return Listing.extend({
        defaults: {
            campaignFormName:
                '${ $.ns }.${ $.ns }.campaign_edit_container.campaign_edit_modal.aw_followupemail2_campaign_form',
            campaignFormModalName: '${ $.ns }.${ $.ns }.campaign_edit_container.campaign_edit_modal',
            template: 'Aheadworks_Followupemail2/ui/campaign/listing',
            rowTmpl: 'Aheadworks_Followupemail2/ui/campaign/row',
            editCampaignTitle: $t('Edit Campaign'),
            newCampaignTitle: $t('New Campaign'),
            confirmDeleteTitle: $t('Are you sure you want to delete the "{CampaignName}" campaign?'),
            confirmDeleteContent:
                $t('All the campaign events and emails will be removed. This action cannot be reversed.'),
            userGuideText: '',
            userGuideLink: '',
            imports: {
                responseData: '${ $.campaignFormName }:responseData',
            },
            listens: {
                responseData: 'afterFormUpdate',
            },
            modules: {
                campaignForm: '${ $.campaignFormName }',
                campaignFormModal: '${ $.campaignFormModalName }'
            }
        },

        /**
         * Initializes Listing component.
         *
         * @returns {Listing} Chainable.
         */
        initialize: function () {
            this._super()
            _.bindAll(this, 'createCampaignForm');
            return this;
        },

        /**
         * Get readme text
         *
         * @returns {string}
         */
        getReadmeText: function () {
            var readmeText = $t('Click "{ButtonName}" to start.');
            var buttonName = '<strong>' + $t('Create New Campaign') + '</strong>';
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
         * After campaign form update callback
         *
         * @param {Object} response
         */
        afterFormUpdate: function (response) {
            if (response.ajaxExpired) {
                window.location.href = response.ajaxRedirect;
            }

            if (!response.error) {
                this.campaignFormModal().closeModal();
                this.reloadGridData();
            } else {
                this.showError(response.message);
            }
        },

        /**
         * Checks if campaign is inactive
         *
         * @returns {Boolean}
         */
        isInactive: function (row) {
            if (row && row.status == 0) {
                return true;
            }
            return false;
        },

        /**
         * Checks if start date or end date has data
         *
         * @returns {Boolean}
         */
        hasDateSelected: function (row) {
            return !!row && (row.start_date || row.end_date);
        },

        /**
         * Get start date
         *
         * @returns {String}
         */
        getStartDate: function (row) {
            var startDate = null;
            this.visibleColumns.forEach(function (column) {
                if (column.index == 'start_date') {
                    startDate = column.getLabel(row);
                }
            });
            return startDate;
        },

        /**
         * Get end date
         *
         * @returns {String}
         */
        getEndDate: function (row) {
            var endDate = null;
            this.visibleColumns.forEach(function (column) {
                if (column.index == 'end_date') {
                    endDate = column.getLabel(row);
                }
            });
            return endDate;
        },

        /**
         * Redirect to events management page
         */
        openEventsManagement: function (row) {
            var url = this.source.manage_event_url + this.source.manage_event_param + '/' + row.id + '/';
            window.location.href = url;
        },

        /**
         * Reset campaign statistics
         */
        resetStatistics: function (row) {
            if (row && row.id) {
                confirm({
                    title: $.mage.__('Are you sure you want to reset the campaign statistics?'),
                    content: $.mage.__('All the campaign events and emails statistics will be set to 0. This action cannot be reversed.'),
                    actions: {
                        confirm: function() {
                            this.showLoader();
                            makeAjaxRequest(
                                this.source.reset_statistics_url,
                                {id:row.id},
                                this.reloadGridData.bind(this),
                                this.onError.bind(this)
                            );
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
            }
        },

        /**
         * Init create campaign form
         */
        createCampaignForm: function () {
            this.campaignForm().destroyInserted();
            this.campaignFormModal().set('options.title', this.newCampaignTitle);
        },

        /**
         * Init duplicate campaign form
         */
        duplicateCampaign: function (row) {
            this.campaignForm().destroyInserted();
            this.campaignFormModal().set('options.title', this.newCampaignTitle);
            var data = {
                id: row.id,
                duplicate: true
            };
            this.campaignForm().updateData(data);
            this.campaignFormModal().openModal();
        },

        /**
         * Open campaign edit form
         * @param row
         */
        editCampaignForm: function (row) {
            this.campaignForm().destroyInserted();
            this.campaignFormModal().set('options.title', this.editCampaignTitle);
            var data = {id: row.id};
            this.campaignForm().updateData(data);
            this.campaignFormModal().openModal();
        },

        /**
         * Delete campaign
         *
         * @param row
         */
        deleteCampaign: function (row) {
            var self = this;
            confirm({
                title: this.confirmDeleteTitle.replace('{CampaignName}', row.name),
                content: this.confirmDeleteContent,
                actions: {
                    confirm: function(){
                        this.showLoader();
                        makeAjaxRequest(
                            this.source.delete_campaign_url,
                            {id:row.id},
                            this.reloadGridData.bind(this),
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
                    text: $t('Delete Campaign'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        },

        /**
         * Reload grid data
         */
        reloadGridData: function () {
            var provider = registry.get(this.provider);
            provider.set('params.t', Date.now());
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
        }
    });

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
                    successCallback();
                    return true;
                }
                errorCallback(response.message);
                return false;
            },
        });
    }
});
