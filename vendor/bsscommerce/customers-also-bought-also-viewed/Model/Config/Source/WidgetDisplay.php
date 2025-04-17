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
class WidgetDisplay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Slider')], ['value' => 1, 'label' => __('Grid')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Slider'), 1 => __('Grid')];
    }
}
