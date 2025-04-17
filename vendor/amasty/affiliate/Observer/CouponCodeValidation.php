<?php

namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Model\Validator\AffiliateCouponValidator;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

/**
 * Observer for sales_quote_address_collect_totals_before
 */
class CouponCodeValidation implements ObserverInterface
{
    /**
     * @var AffiliateCouponValidator
     */
    private $affiliateCouponValidator;

    public function __construct(AffiliateCouponValidator $affiliateCouponValidator)
    {
        $this->affiliateCouponValidator = $affiliateCouponValidator;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        $code = $quote->getCouponCode();
        if ($code && !$this->affiliateCouponValidator->validate($code)) {
            $quote->setCouponCode('');
        }
    }
}
