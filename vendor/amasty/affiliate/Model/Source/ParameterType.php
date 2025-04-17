<?php

namespace Amasty\Affiliate\Model\Source;

class ParameterType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'code', 'label' => __('Affiliate Code')],
            ['value' => 'id', 'label' => __('Affiliate ID')],
        ];
    }
}
