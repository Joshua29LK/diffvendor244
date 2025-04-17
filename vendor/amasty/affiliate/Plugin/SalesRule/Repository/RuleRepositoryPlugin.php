<?php

namespace Amasty\Affiliate\Plugin\SalesRule\Repository;

use Magento\SalesRule\Model\RuleRepository;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\Coupon;
use \Magento\Framework\Exception\CouldNotDeleteException;

class RuleRepositoryPlugin
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Coupon
     */
    private $couponResource;

    public function __construct(
        CollectionFactory $collectionFactory,
        Coupon $couponResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->couponResource = $couponResource;
    }

    /**
     * @param RuleRepository $subject
     * @param $id
     *
     * @throws CouldNotDeleteException
     */
    public function beforeDeleteById(RuleRepository $subject, $id)
    {
        $ruleCouponsCollection = $this->collectionFactory->create();
        $ruleCouponsCollection->addFieldToFilter('rule_id', $id);
        /** @var \Magento\SalesRule\Api\Data\CouponInterface $ruleCoupon */
        foreach ($ruleCouponsCollection as $ruleCoupon) {
            $program = $this->couponResource->getProgramId($ruleCoupon->getCode());
            if ($program) {
                throw new CouldNotDeleteException(
                    __('Can\'t delete rule with id (%1). Please remove association with affiliate program.', $id)
                );
            }
        }
    }
}
