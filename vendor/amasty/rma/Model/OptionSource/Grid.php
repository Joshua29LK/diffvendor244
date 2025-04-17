<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class Grid implements ArrayInterface
{
    public const MANAGE = 0;
    public const PENDING = 1;
    public const ARCHIVED = 2;

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
            self::MANAGE => __('Manage Requests'),
            self::PENDING => __('Customers\' Pending Requests'),
            self::ARCHIVED => __('Archived Requests')
        ];
    }
}
