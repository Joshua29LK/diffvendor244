<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Campaign;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class CancelButton
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Campaign
 */
class CancelButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Cancel'),
            'class' => 'cancel',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aw_followupemail2_campaign_listing.aw_followupemail2_campaign_listing'
                                    . '.campaign_edit_container.campaign_edit_modal',
                                'actionName' => 'closeModal'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 20
        ];
    }
}
