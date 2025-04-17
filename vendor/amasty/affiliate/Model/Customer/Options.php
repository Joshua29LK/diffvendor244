<?php

namespace Amasty\Affiliate\Model\Customer;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use \Magento\Customer\Ui\Component\Listing\Column\Group\Options as CustomerGroupOptions;

class Options extends AbstractSource
{

    /**
     * @var CustomerGroupOptions
     */
    private $customerGroupOptions;

    public function __construct(
        CustomerGroupOptions $customerGroupOptions
    ) {
        $this->customerGroupOptions = $customerGroupOptions;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->customerGroupOptions->toOptionArray();
    }
}
