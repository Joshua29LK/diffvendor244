<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Transaction;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Model\RegistryConstants;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AddValidator
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Collection
     */
    private $couponCollection;

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        CustomerRepositoryInterface $customerRepository,
        CookieManagerInterface $cookieManager,
        Collection $couponCollection
    ) {
        $this->accountRepository = $accountRepository;
        $this->customerRepository = $customerRepository;
        $this->cookieManager = $cookieManager;
        $this->couponCollection = $couponCollection;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function canAddTransaction(OrderInterface $order): bool
    {
        $account = $this->getAccount($order);

        if ($account) {
            try {
                $customer = $this->customerRepository->getById((int)$account->getCustomerId());

                return $customer->getEmail() !== $order->getCustomerEmail();
            } catch (LocalizedException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     * @return AccountInterface|null
     */
    private function getAccount(OrderInterface $order): ?AccountInterface
    {
        $couponCode = $order->getCouponCode();
        $account = null;

        try {
            if ($couponCode !== null && $this->couponCollection->isAffiliateCoupon($couponCode)) {
                $account = $this->accountRepository->getByCouponCode($couponCode);
            } else {
                $affiliateCode = $this->cookieManager->getCookie(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE);

                if ($affiliateCode !== null) {
                    $account = $this->accountRepository->getByReferringCode($affiliateCode);
                }
            }
        } catch (LocalizedException $e) {
            $account = null;
        }

        return $account;
    }
}
