<?php

namespace Amasty\Affiliate\Model\ResourceModel;

class Coupon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * Coupon constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Coupon\CollectionFactory $couponCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        $connectionName = null
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_coupon', 'entity_id');
    }

    /**
     * @param $coupon
     * @return mixed
     */
    public function getProgramId($coupon)
    {
        $coupons = $this->couponCollectionFactory->create()->addCouponFilter($coupon);
        $coupon = $coupons->getFirstItem()->getProgramId();

        return $coupon;
    }

    /**
     * @param $programId
     * @param $accountId
     * @return \Magento\Framework\DataObject
     */
    public function getEntity($programId, $accountId)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $coupons */
        $coupons = $this->couponCollectionFactory->create()
            ->addFilterForCoupon($programId, $accountId);

        return $coupons->getFirstItem();
    }
}
