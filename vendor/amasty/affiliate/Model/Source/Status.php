<?php

namespace Amasty\Affiliate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            '0' => __('Disabled'),
            '1' => __('Enabled'),
        ];
    }
}
