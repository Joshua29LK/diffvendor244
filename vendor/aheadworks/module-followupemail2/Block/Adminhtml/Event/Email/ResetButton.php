<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Event\Email;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetButton
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Event\Email
 */
class ResetButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aw_followupemail2_manage_events_form'
                                    . '.aw_followupemail2_manage_events_form.data.events',
                                'actionName' => 'resetEmailForm'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 30
        ];
    }
}
