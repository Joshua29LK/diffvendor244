<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\OptionSource;

use \Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection;

class CustomerGroups implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $customerGroupCollection;

    public function __construct(
        Collection $customerGroupCollection
    ) {
        $this->customerGroupCollection = $customerGroupCollection;
    }

    public function toOptionArray()
    {
        return $this->customerGroupCollection->toOptionArray();
    }
}
