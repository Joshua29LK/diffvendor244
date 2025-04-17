<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\CABAV\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class WidgetSortViewed implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Random')],
            ['value' => 1, 'label' => __('Product Name (A-Z)')],
            ['value' => 2, 'label' => __('Product Name (Z-A)')],
            ['value' => 3, 'label' => __('Product Price (High-Low)')],
            ['value' => 4, 'label' => __('Product Price (Low-High)')],
            ['value' => 5, 'label' => __('Viewed Times of Product')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Random'),
            1 => __('Product Name (A-Z)'),
            2 => __('Product Name (Z-A)'),
            3 => __('Product Price (High-Low)'),
            4 => __('Product Price (Low-High)'),
            5 => __('Viewed Times of Product')
        ];
    }
}
