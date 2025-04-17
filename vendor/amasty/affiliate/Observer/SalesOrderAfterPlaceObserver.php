<?php

namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Model\ResourceModel\Coupon;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\Transaction\AddValidator;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Collection
     */
    private $couponCollection;

    /**
     * @var AddValidator
     */
    private $addValidator;

    public function __construct(
        CollectionFactory $collectionFactory,
        CartRepositoryInterface $cartRepository,
        Coupon $coupon,
        Collection $couponCollection,
        AddValidator $addValidator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->cartRepository = $cartRepository;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
        $this->addValidator = $addValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        if (!$this->addValidator->canAddTransaction($order)) {
            return $this;
        }

        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($order->getQuoteId());

        /** @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programs */
        $programs = $this->collectionFactory->create()->getProgramsByRuleIds($quote->getAppliedRuleIds());
        $programs->addActiveFilter();
        $couponCode = $order->getCouponCode();
        if ($couponCode
            && $this->couponCollection->isAffiliateCoupon($couponCode)
            && $this->coupon->getProgramId($couponCode)
        ) {
            $programs->addProgramIdFilter($this->coupon->getProgramId($couponCode));
        }

        /** @var \Amasty\Affiliate\Model\Program $program */
        foreach ($programs as $program) {
            $program->addTransaction($order);
        }

        return $this;
    }
}
