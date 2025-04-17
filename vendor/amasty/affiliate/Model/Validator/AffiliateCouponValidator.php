<?php

namespace Amasty\Affiliate\Model\Validator;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AffiliateCouponValidator
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
     * @var CollectionFactory
     */
    private $programCollectionFactory;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $programCollectionFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->customerRepository = $customerRepository;
        $this->programCollectionFactory = $programCollectionFactory;
    }

    /**
     * @param string $couponCode
     * @return bool
     */
    public function validate($couponCode)
    {
        if (!array_key_exists($couponCode, $this->cache)) {
            $this->cache[$couponCode] = $this->getValidationResult($couponCode);
        }

        return $this->cache[$couponCode];
    }

    /**
     * @param string $couponCode
     * @return bool
     */
    private function getValidationResult($couponCode)
    {
        try {
            $account = $this->accountRepository->getByCouponCode($couponCode);
            if (!$account->getIsAffiliateActive()) {
                return false;
            }
        } catch (NoSuchEntityException $e) {
            return true;
        }

        try {
            $customer = $this->customerRepository->getById($account->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        $collection = $this->programCollectionFactory->create();
        $collection->addCouponFilter($couponCode);
        $collection->addCustomerAndGroupFilter(
            $customer->getId(),
            $customer->getGroupId()
        );
        $collection->addOrderCounterFilter((int)$account->getAccountId());
        $collection->addActiveFilter();

        return $collection->getSize() > 0;
    }
}
