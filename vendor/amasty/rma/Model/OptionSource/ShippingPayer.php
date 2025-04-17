<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class ShippingPayer implements ArrayInterface
{
    public const CUSTOMER = 0;
    public const STORE_OWNER = 1;
    public const DO_NOT_SHOW = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::CUSTOMER => __('Customer'),
            self::STORE_OWNER => __('Store Owner'),
            self::DO_NOT_SHOW => __('Do not show')
        ];
    }
}
