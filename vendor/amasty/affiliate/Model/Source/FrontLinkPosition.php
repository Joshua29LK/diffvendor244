<?php

namespace Amasty\Affiliate\Model\Source;

class FrontLinkPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'top', 'label' => __('Top Menu')],
            ['value' => 'bottom', 'label' => __('Bottom Menu')],
        ];
    }
}
