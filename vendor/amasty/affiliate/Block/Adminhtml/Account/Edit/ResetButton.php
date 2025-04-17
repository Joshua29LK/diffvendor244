<?php

namespace Amasty\Affiliate\Block\Adminhtml\Account\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ResetButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('reset')) {
            $data = [
                'label' => __('Reset'),
                'class' => 'reset',
                'on_click' => 'location.reload();',
                'sort_order' => 30,
            ];
        }
        return $data;
    }
}
