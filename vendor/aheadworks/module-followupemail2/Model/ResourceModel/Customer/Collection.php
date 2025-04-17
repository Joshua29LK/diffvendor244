<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Customer;

use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Customer
 */
class Collection extends CustomerCollection
{
    /**
     * Add birthday date filter
     *
     * @param string $birthdayDate
     * @return $this
     */
    public function addBirthdayFilter($birthdayDate)
    {
        $this->getSelect()
            ->where('DATE_FORMAT(dob, "%m-%d")=?', $birthdayDate);

        return $this;
    }
}
