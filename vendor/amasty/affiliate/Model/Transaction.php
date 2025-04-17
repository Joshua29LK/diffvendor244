<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Api\ProgramRepositoryInterface;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Api\WithdrawalRepositoryInterface;
use Amasty\Affiliate\Model\CommissionCalculation\AvailableCommissionCalculator;
use Amasty\Affiliate\Model\ResourceModel\Coupon as AffiliateCoupon;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;
use Amasty\Affiliate\Model\ResourceModel\Program\OrderCounter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Model\Order;

class Transaction extends \Magento\Framework\Model\AbstractModel implements TransactionInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_READY_FOR_PER_PROFIT = 'ready_for_per_profit';
    public const STATUS_ORDER_LIMIT_EXCEEDED = 'order_limit_exceeded';

    public const TYPE_PER_PROFIT = 'per_profit';
    public const TYPE_PER_SALE = 'per_sale';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_FOR_FUTURE_PER_PROFIT = 'for_future_per_profit';

    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProgramRepositoryInterface
     */
    protected $programRepository;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Program
     */
    protected $program;

    /**
     * @var float
     */
    protected $currentProfit;

    /**
     * @var float
     */
    protected $ordersProfit;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Mailsender
     */
    protected $mailsender;

    /**
     * @var ResourceModel\Coupon
     */
    protected $coupon;

    /**
     * @var ResourceModel\Coupon\Collection
     */
    protected $couponCollection;

    /**
     * @var WithdrawalRepositoryInterface
     */
    protected $withdrawalRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PriceConverter
     */
    protected $priceConverter;

    /**
     * @var OrderCounter
     */
    protected $orderCounter;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CommissionCalculation\AvailableCommissionCalculator
     */
    private $commissionCalculator;

    public function __construct(
        Context $context,
        Registry $registry,
        TransactionRepositoryInterface $transactionRepository,
        AccountRepositoryInterface $accountRepository,
        ProgramRepositoryInterface $programRepository,
        ScopeConfigInterface $scopeConfig,
        TransactionFactory $transactionFactory,
        CookieManagerInterface $cookieManager,
        Mailsender $mailsender,
        AffiliateCoupon $coupon,
        Collection $couponCollection,
        WithdrawalRepositoryInterface $withdrawalRepository,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        PriceConverter $priceConverter,
        AvailableCommissionCalculator $commissionCalculator,
        OrderCounter $orderCounter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->scopeConfig = $scopeConfig;
        $this->programRepository = $programRepository;
        $this->transactionFactory = $transactionFactory;
        $this->cookieManager = $cookieManager;
        $this->mailsender = $mailsender;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
        $this->withdrawalRepository = $withdrawalRepository;
        $this->customerRepository = $customerRepository;
        $this->priceConverter = $priceConverter;
        $this->orderCounter = $orderCounter;
        $this->customerSession = $customerSession;
        $this->commissionCalculator = $commissionCalculator;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Transaction::class);
        $this->setIdFieldName('transaction_id');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_CANCELED => __('Canceled'),
            self::STATUS_ON_HOLD => __('On Hold')
        ];
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_PER_PROFIT => __('Per Profit'),
            self::TYPE_PER_SALE => __('Per Sale'),
            self::TYPE_WITHDRAWAL => __('Withdrawal')
        ];
    }

    /**
     * @return Transaction
     */
    public function getPerProfitTransaction()
    {
        /** @var ResourceModel\Transaction\Collection $collection */
        $collection = $this->getResourceCollection();
        $perProfitTransaction = $collection->getPerProfitTransaction($this);

        return $perProfitTransaction;
    }

    /**
     * Check on hold transactions
     */
    public function completeHoldTransactions()
    {
        /** @var ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->getResourceCollection();
        $transactionCollection->addHoldFilter();
        /** @var Transaction $transaction */
        foreach ($transactionCollection as $transaction) {
            $transaction->complete(true);
        }
    }

    /**
     * Complete transaction
     * @param bool $removeOnHold
     */
    public function complete($removeOnHold = false)
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->get($this->getAffiliateAccountId());

        $status = self::STATUS_COMPLETED;
        $this->prepareTransactionInformation($account, $status);

        $this->setStatus($status);
        $this->setBalance($account->getBalance());
        if ($removeOnHold) {
            $account->setOnHold($account->getOnHold() - $this->getCommission());
        }

        if ($status === self::STATUS_ORDER_LIMIT_EXCEEDED) {
            $this->setCommission(0);
        }

        $this->transactionRepository->save($this);
        $this->accountRepository->save($account);
        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT
            && $this->getCurrentProgram()->getWithdrawalType() == self::TYPE_PER_PROFIT
        ) {
            $this->addCommissionByProfit();
        }
    }

    /**
     * Check if should add profit to account by profit and add it
     */
    public function addCommissionByProfit()
    {
        /** @var ResourceModel\Transaction\Collection $collection */
        $collection = $this->getResourceCollection();
        $collection->addForFutureFilter($this->getProgramId(), $this->getAffiliateAccountId());
        $profitCollection = clone $collection;
        $this->ordersProfit = $profitCollection->getProfit();
        /** @var Order $currentOrder */
        $currentOrder = $this->_registry->registry(RegistryConstants::CURRENT_ORDER);
        if (!$currentOrder->getEntityId()) {
            $this->ordersProfit += $currentOrder->getBaseSubtotal() + $currentOrder->getBaseDiscountAmount();
        }
        $program = $this->programRepository->get($this->getProgramId());
        /** @var \Amasty\Affiliate\Model\Coupon $profitCouponEntity */
        $profitCouponEntity = $this->coupon->getEntity($program->getProgramId(), $this->getAffiliateAccountId());
        $this->currentProfit = $profitCouponEntity->getCurrentProfit();
        $allProfit = $this->currentProfit + $this->ordersProfit;
        if ($allProfit > $program->getCommissionPerProfitAmount()) {
            /** @var Transaction $newTransaction */
            $newTransaction = $this->transactionFactory->create();
            $newTransaction->ordersProfit = $this->ordersProfit;
            $newTransaction->newTransaction(
                $program,
                $this->_registry->registry(RegistryConstants::CURRENT_ORDER),
                self::STATUS_COMPLETED,
                self::TYPE_PER_PROFIT,
                $this->getAffiliateAccountId()
            );
            $newTransaction->complete();
            $collection->setStatus(self::STATUS_COMPLETED);
            $profitCouponEntity->setCurrentProfit($allProfit - $program->getCommissionPerProfitAmount());
            $this->coupon->save($profitCouponEntity);
        } else {
            if ($this->ordersProfit !== null) {
                $this->setProfit($this->ordersProfit);
                $this->transactionRepository->save($this);
            }
        }
    }

    /**
     * Put transaction on hold
     */
    public function onHold()
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->get($this->getAffiliateAccountId());

        $this->setStatus(Transaction::STATUS_ON_HOLD);
        $account->setOnHold($account->getOnHold() + $this->getCommission());

        $this->transactionRepository->save($this);
        $this->accountRepository->save($account);
    }

    //TODO: need refactoring this method
    /**
     * @param Program $program
     * @param Order $order
     * @param $status
     * @param null $type
     * @param null $accountId
     * @return $this
     */
    public function newTransaction($program, $order, $status, $type = null, $accountId = null)
    {
        $this->program = $this->programRepository->get($program->getId());
        $this->order = $order;

        if ($this->order !== null) {
            $couponCode = $this->order->getCouponCode();
            if ($couponCode != null && $this->couponCollection->isAffiliateCoupon($couponCode)) {
                $account = $this->accountRepository->getByCouponCode($couponCode);
            } else {
                $affiliateCode = $this->cookieManager
                    ->getCookie(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE);
                if ($affiliateCode != null) {
                    $account = $this->accountRepository->getByReferringCode($affiliateCode);
                    if (!empty($program->getAvailableGroups()) || !empty($program->getAvailableCustomers())) {
                        $customer = $this->customerRepository->getById($account->getCustomerId());
                        if (!in_array($customer->getGroupId(), explode(',', $program->getAvailableGroups()))
                            && !in_array($customer->getId(), explode(',', $program->getAvailableCustomers()))) {
                            return $this;
                        }
                    }
                } else {
                    return $this;
                }
            }
            $discount = $this->order->getBaseDiscountAmount();
            $orderId = $this->order->getIncrementId();
        } else {
            if ($accountId != null) {
                $account = $this->accountRepository->get($accountId);
                $discount = null;
                $orderId = null;
            } else {
                return $this;
            }
        }

        if ($program->getIsLifetime() && $this->order !== null) {
            $customerLifetimeAccountId = $this->getCustomerLifetimeAccountId();
            if ($customerLifetimeAccountId !== null) {

                $lifetimeAccount = $this->accountRepository->get($customerLifetimeAccountId);

                if ($lifetimeAccount) {
                    $customer = $this->customerRepository->getById($lifetimeAccount->getCustomerId());

                    //We don't add commission for affiliate if current affiliate's order placed from lifetime account
                    if ($customer->getEmail() !== $order->getCustomerEmail()) {
                        $account = $lifetimeAccount;
                    }
                }
            }
        }

        if (!$account->getIsAffiliateActive()) {
            return $this;
        }

        $commission = $this->calculateCommission();

        if ($type === null) {
            $type = $program->getWithdrawalType();
        }

        $data = [
            'affiliate_account_id' => $account->getAccountId(),
            'program_id' => $program->getProgramId(),
            'order_increment_id' => $orderId,
            'profit' => $this->ordersProfit,
            'balance' => $account->getBalance(),
            'commission' => $commission,
            'discount' => $discount,
            'type' => $type,
            'status' => $status
        ];

        $this->setData($data);
        $this->transactionRepository->save($this);

        $this->accountRepository->save($account);

        if (in_array($this->getType(), [self::TYPE_PER_PROFIT, self::TYPE_PER_SALE])) {
            $this->sendEmail(Mailsender::TYPE_AFFILIATE_TRANSACTION_NEW);
        }

        return $this;
    }

    /**
     * Send email about transaction
     * @param string $type
     */
    public function sendEmail($type)
    {
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/' . $type)) {
            /** @var \Amasty\Affiliate\Model\Account $account */
            $account = $this->accountRepository->get($this->getAffiliateAccountId());
            if ($account->getReceiveNotifications()) {
                $emailData = $this->getData();
                $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();

                $this->mailsender->sendAffiliateMail(
                    $emailData,
                    $type,
                    $account->getEmail(),
                    $account
                );
            }
        }
    }

    /**
     * @return int|null
     */
    protected function getCustomerLifetimeAccountId()
    {
        $customerLifetimeAccountId = null;

        /** @var ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getCustomerProgramTransactions();

        /** @var Transaction $transaction */
        $transaction = $transactions->getFirstItem();

        if ($transaction->getAffiliateAccountId()) {
            $customerLifetimeAccountId = $transaction->getAffiliateAccountId();
        }

        return $customerLifetimeAccountId;
    }

    /**
     * @return ResourceModel\Transaction\Collection
     */
    protected function getCustomerProgramTransactions()
    {
        /** @var ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getResourceCollection();
        $transactions->addCustomerProgramFilter(
            $this->order->getCustomerEmail(),
            $this->getCurrentProgram()->getProgramId()
        );

        return $transactions;
    }

    /**
     * @return float|int
     */
    protected function calculateCommission()
    {
        /** @var Program $program */
        $program = $this->getCurrentProgram();

        $value = $program->getCommissionValue();
        $type = $program->getCommissionValueType();

        if ($program->getWithdrawalType() != self::TYPE_PER_PROFIT
            && $program->getFromSecondOrder()
            && $this->isSecondOrder()
        ) {
            $value = $program->getCommissionValueSecond();
            $type = $program->getCommissionTypeSecond();
        }

        if ($type == Program::COMMISSION_TYPE_PERCENT) {
            if ($program->getWithdrawalType() == self::TYPE_PER_PROFIT) {
                $value = $value / 100 * $program->getCommissionPerProfitAmount();
            } elseif ($this->ordersProfit) {
                $value = ($value / 100) * $this->ordersProfit;
            } else {
                $value = ($value / 100) * $this->commissionCalculator->calculate($program, $this->order);
            }
        }

        return $value;
    }

    /**
     * @return bool
     */
    protected function isSecondOrder()
    {
        $isSecondOrder = false;

        /** @var ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getCustomerProgramTransactions($this->order);

        if ($transactions->count() > 0) {
            $isSecondOrder = true;
        }

        return $isSecondOrder;
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface|Program
     */
    protected function getCurrentProgram()
    {
        if (!isset($this->program)) {
            $this->program = $this->programRepository->get($this->getProgramId());
        }

        return $this->program;
    }

    /**
     * Checking if the limit of placed orders is exceeded
     *
     * @param int $programId
     * @param int $affiliateAccountId
     * @param int $orderLimit
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isOrderLimitExceeded(int $programId, int $affiliateAccountId, int $orderLimit): bool
    {
        return $orderLimit > 0
            && $this->orderCounter->getProgramOrderCounter($programId, $affiliateAccountId) >= $orderLimit;
    }

    /**
     * @param AccountInterface $account
     * @param string $status
     */
    private function prepareTransactionInformation(AccountInterface $account, string &$status): void
    {
        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT
            && $this->getCurrentProgram()->getWithdrawalType() == self::TYPE_PER_SALE
        ) {
            $this->setType(self::TYPE_PER_SALE);
        }

        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT) {
            $status = self::STATUS_READY_FOR_PER_PROFIT;
        } elseif ($this->isOrderLimitProcessed($status)) {
            $account->setBalance($account->getBalance() + $this->getCommission());
            $account->setLifetimeCommission($account->getLifetimeCommission() + $this->getCommission());
            $account->setAvailable($account->getAvailable() + $this->getCommission());
        }
    }

    /**
     * @param string $status
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isOrderLimitProcessed(string &$status): bool
    {
        if ($this->getCurrentProgram()->getWithdrawalType() != self::TYPE_PER_SALE) {
            return true;
        }

        if ($this->isOrderLimitExceeded(
            (int)$this->getProgramId(),
            (int)$this->getAffiliateAccountId(),
            (int)$this->getCurrentProgram()->getRestrictTransactionsToNumberOrders()
        )) {
            $status = self::STATUS_ORDER_LIMIT_EXCEEDED;

            return false;
        }

        $this->orderCounter->incrementProgramOrderCounter(
            (int)$this->getProgramId(),
            (int)$this->getAffiliateAccountId()
        );

        return true;
    }

    /**
     * @return int|null
     */
    public function getTransactionId()
    {
        return $this->_getData(TransactionInterface::TRANSACTION_ID);
    }

    /**
     * @param int $transactionId
     * @return $this|TransactionInterface
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(TransactionInterface::TRANSACTION_ID, $transactionId);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAffiliateAccountId()
    {
        return $this->_getData(TransactionInterface::AFFILIATE_ACCOUNT_ID);
    }

    /**
     * @param int $affiliateAccountId
     * @return $this|TransactionInterface
     */
    public function setAffiliateAccountId($affiliateAccountId)
    {
        $this->setData(TransactionInterface::AFFILIATE_ACCOUNT_ID, $affiliateAccountId);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProgramId()
    {
        return $this->_getData(TransactionInterface::PROGRAM_ID);
    }

    /**
     * @param int $programId
     * @return $this|TransactionInterface
     */
    public function setProgramId($programId)
    {
        $this->setData(TransactionInterface::PROGRAM_ID, $programId);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderIncrementId()
    {
        return $this->_getData(TransactionInterface::ORDER_INCREMENT_ID);
    }

    /**
     * @param string|null $orderIncrementId
     * @return $this|TransactionInterface
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(TransactionInterface::ORDER_INCREMENT_ID, $orderIncrementId);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getProfit()
    {
        return $this->_getData(TransactionInterface::PROFIT);
    }

    /**
     * @param float|null $profit
     * @return $this|TransactionInterface
     */
    public function setProfit($profit)
    {
        $this->setData(TransactionInterface::PROFIT, $profit);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getBalance()
    {
        return $this->_getData(TransactionInterface::BALANCE);
    }

    /**
     * @param float|null $balance
     * @return $this|TransactionInterface
     */
    public function setBalance($balance)
    {
        $this->setData(TransactionInterface::BALANCE, $balance);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCommission()
    {
        return $this->_getData(TransactionInterface::COMMISSION);
    }

    /**
     * @param float|null $commission
     * @return $this|TransactionInterface
     */
    public function setCommission($commission)
    {
        $this->setData(TransactionInterface::COMMISSION, $commission);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDiscount()
    {
        return $this->_getData(TransactionInterface::DISCOUNT);
    }

    /**
     * @param float|null $discount
     * @return $this|TransactionInterface
     */
    public function setDiscount($discount)
    {
        $this->setData(TransactionInterface::DISCOUNT, $discount);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData(TransactionInterface::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return $this|TransactionInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(TransactionInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->_getData(TransactionInterface::TYPE);
    }

    /**
     * @param string $type
     * @return $this|TransactionInterface
     */
    public function setType($type)
    {
        $this->setData(TransactionInterface::TYPE, $type);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_getData(TransactionInterface::STATUS);
    }

    /**
     * @param string $status
     * @return $this|TransactionInterface
     */
    public function setStatus($status)
    {
        $this->setData('previous_status', $this->getStatus());
        $this->setData(TransactionInterface::STATUS, $status);

        return $this;
    }

    /**
     * @return int
     */
    public function getBalanceChangeType()
    {
        return (int)$this->_getData(TransactionInterface::BALANCE_CHANGE_TYPE);
    }

    /**
     * @param int $type
     * @return $this|TransactionInterface
     */
    public function setBalanceChangeType($type)
    {
        $this->setData(TransactionInterface::BALANCE_CHANGE_TYPE, $type);

        return $this;
    }
}
