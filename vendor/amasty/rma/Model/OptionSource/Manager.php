<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class Manager implements ArrayInterface
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $managerCollectionFactory;

    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $managerCollectionFactory
    ) {
        $this->managerCollectionFactory = $managerCollectionFactory;
    }

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
        $result = [0 => __('Unassigned')];
        $managerCollection = $this->managerCollectionFactory->create();
        $managerCollection->addFieldToFilter('main_table.is_active', 1)//TODO is_active?
            ->addFieldToSelect(['user_id', 'username']);

        foreach ($managerCollection->getData() as $manager) {
            $result[$manager['user_id']] = $manager['username'];
        }

        return $result;
    }
}
