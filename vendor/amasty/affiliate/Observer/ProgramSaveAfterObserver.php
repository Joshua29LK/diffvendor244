<?php


namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Model\CouponCreator;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProgramSaveAfterObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;
    /**
     * @var CouponCreator
     */
    private $couponCreator;

    public function __construct(
        CollectionFactory $accountCollectionFactory,
        CouponCreator $couponCreator
    ) {
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->couponCreator = $couponCreator;
    }

    public function execute(EventObserver $observer)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Account\Collection $accountCollection */
        $accountCollection = $this->accountCollectionFactory->create();
        /** @var \Amasty\Affiliate\Model\Account $account */
        foreach ($accountCollection as $account) {
            $this->couponCreator->generateCoupons($observer->getObject(), $account->getAccountId());
        }
    }
}
