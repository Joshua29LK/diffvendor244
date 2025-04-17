<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Event\Email;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Event\Email
 */
class SaveAndContinueButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinue'],
                ],
            ],
            'sort_order' => 20,
        ];
    }
}
