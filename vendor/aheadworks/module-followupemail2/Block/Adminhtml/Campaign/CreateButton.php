<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Campaign;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class CreateButton
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Campaign
 */
class CreateButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Create New Campaign'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aw_followupemail2_campaign_listing.aw_followupemail2_campaign_listing'
                                    . '.aw_followupemail2_campaign_columns',
                                'actionName' => 'createCampaignForm'
                            ],
                            [
                                'targetName' => 'aw_followupemail2_campaign_listing.aw_followupemail2_campaign_listing'
                                    . '.campaign_edit_container.campaign_edit_modal',
                                'actionName' => 'openModal'
                            ],
                            [
                                'targetName' => 'aw_followupemail2_campaign_listing.aw_followupemail2_campaign_listing'
                                    . '.campaign_edit_container.campaign_edit_modal.aw_followupemail2_campaign_form',
                                'actionName' => 'render'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 10
        ];
    }
}
