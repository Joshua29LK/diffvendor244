<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory;

class CouponListProvider
{
    /**
     * @var ResourceModel\Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    public function __construct(CollectionFactory $couponCollectionFactory)
    {
        $this->couponCollectionFactory = $couponCollectionFactory;
    }

    /**
     * @param int $accountId
     * @param int $customerId
     * @param \Magento\Customer\Model\Session $customerSession
     * @param bool $isSystem
     * @param null|int $programId
     * @return ResourceModel\Coupon\Collection
     */
    public function getCollectionForFrontend($accountId, $customerId, $customerSession, $isSystem, $programId = null)
    {
        $couponCollection = $this->couponCollectionFactory->create();
        $couponCollection->addAccountIdFilter($accountId);
        $couponCollection->addProgramActiveFilter();
        if ($programId) {
            $couponCollection->addFieldToFilter('main_table.program_id', $programId);
        }
        $couponCollection->addProgramCustomerAndGroupIdFilter(
            $customerId,
            $customerSession->getCustomerGroupId()
        )->addFieldToFilter('is_system', $isSystem);

        return $couponCollection;
    }

    /**
     * @param int $accountId
     * @param int $programId
     * @return ResourceModel\Coupon\Collection
     */
    public function getCustomCouponCollection($accountId, $programId)
    {
        return $this->couponCollectionFactory->create()
            ->addFieldToFilter('main_table.program_id', $programId)
            ->addFieldToFilter('main_table.account_id', $accountId)
            ->addFieldToFilter('main_table.is_system', false);
    }

    /**
     * @param int $accountId
     * @param int $programId
     * @return string
     */
    public function getCustomCouponListAsString($accountId, $programId)
    {
        $customCouponsCollection = $this->getCustomCouponCollection($accountId, $programId);
        $string = '';
        foreach ($customCouponsCollection->getData() as $customCoupon) {
            $firstItemId = $customCouponsCollection->getFirstItem()->getId();
            if ($firstItemId == $customCoupon['entity_id']) {
                $string .= $customCoupon['code'];
            } else {
                $string .= ', ' . $customCoupon['code'];
            }
        }

        return $string;
    }
}
