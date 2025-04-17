<?php

namespace Amasty\Affiliate\Plugin\Promo\Model\ResourceModel\Rule;

use Amasty\Affiliate\Model\RegistryConstants;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\DB\Select;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Quote\Model\QuoteRepository;

class Collection
{
    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory
     */
    private $programCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var State
     */
    private $scopeManager;

    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $programCollectionFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        State $scopeManager
    ) {
        $this->programCollectionFactory = $programCollectionFactory;
        $this->cookieManager = $cookieManager;
        $this->accountRepository = $accountRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->scopeManager = $scopeManager;
    }

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     */
    public function beforeLoad(
        \Magento\SalesRule\Model\ResourceModel\Rule\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        if ($subject->isLoaded()) {
            return;
        }
        $account = $this->getAffiliateAccount();
        if (!$account) {
            return;
        }

        $select = $subject->getSelect();
        $whereParts = $select->getPart(Select::WHERE);
        $customer = $this->customerRepository->getById($account->getCustomerId());

        $affiliateRuleIds = $this->programCollectionFactory
            ->create()
            ->addActiveFilter()
            ->addCustomerAndGroupFilter($customer->getId(), $customer->getGroupId())
            ->addOrderCounterFilter((int)$account->getAccountId())
            ->getColumnValues('rule_id');

        if (!$affiliateRuleIds) {
            return;
        }

        $affiliateRuleIds = implode("','", $affiliateRuleIds);
        foreach ($whereParts as $key => $wherePart) {
            if ($wherePart === "AND (`main_table`.`coupon_type` = '1')"
                || $wherePart === 'AND (main_table.coupon_type = 1)'
            ) {
                $whereParts[$key] = "AND ((`main_table`.`coupon_type` = '1') 
                    OR main_table.rule_id IN ('{$affiliateRuleIds}'))";
            }
        }

        $select->setPart(Select::WHERE, $whereParts);
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\AccountInterface|null
     */
    private function getAffiliateAccount()
    {
        $affiliateAccount = null;
        $couponCode = null;
        if ($this->checkoutSession->getQuoteId()) {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuoteId());
            $couponCode = $quote->getCouponCode();
        }
        if (!empty($couponCode)) {
            try {
                $account = $this->accountRepository->getByCouponCode($couponCode);
                if ($account->getIsAffiliateActive()) {
                    $affiliateAccount = $account;
                }
            } catch (NoSuchEntityException $e) {
                unset($e);
            }
        } else {
            $affiliateCode = $this->cookieManager
                ->getCookie(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE);
            if ($affiliateCode !== null && $this->scopeManager->getAreaCode() !== 'adminhtml') {
                try {
                    $account = $this->accountRepository->getByReferringCode($affiliateCode);
                    if ($account->getIsAffiliateActive()) {
                        $affiliateAccount = $account;
                    }
                } catch (NoSuchEntityException $e) {
                    unset($e);
                }
            }
        }

        return $affiliateAccount;
    }
}
