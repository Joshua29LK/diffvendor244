<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;
use Amasty\Affiliate\Api\Data\ProgramInterface;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Action\CollectionFactory;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @method \Amasty\Affiliate\Model\ResourceModel\Program _getResource()
 */
class Program extends AbstractModel implements ProgramInterface
{
    public const DATA_PERSISTOR_KEY = 'amasty_affiliate_program';

    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    public const COMMISSION_TYPE_FIXED = 'fixed';
    public const COMMISSION_TYPE_PERCENT = 'percent';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_affiliate_program';

    /**
     * @var CombineFactory
     */
    private $combineFactory;

    /**
     * @var CollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var ResourceModel\Transaction\CollectionFactory
     */
    private $transactionsCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceModel\Coupon\Collection
     */
    private $couponCollection;

    public function __construct(
        Context $context,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CurrencyInterface $localeCurrency,
        StoreManagerInterface $storeManager,
        TransactionRepositoryInterface $transactionRepository,
        TransactionFactory $transactionFactory,
        ResourceModel\Transaction\CollectionFactory $transactionsCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Collection $couponCollection,
        $resource = null,
        $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->transactionRepository = $transactionRepository;
        $this->transactionFactory = $transactionFactory;
        $this->transactionsCollectionFactory = $transactionsCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->couponCollection = $couponCollection;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Affiliate\Model\ResourceModel\Program::class);
        $this->setIdFieldName('program_id');
    }

    /**
     * Prepare commission values in dependency on type
     */
    public function preparePrices()
    {
        $this->preparePriceValues($this->getCommissionValueType(), $this->getCommissionValue(), 'commission_value');
        $this->preparePriceValues(
            $this->getCommissionTypeSecond(),
            $this->getCommissionValueSecond(),
            'commission_value_second'
        );
        $this->preparePriceValues($this->getDiscountType(), $this->getBaseDiscountAmount(), 'discount_amount');
    }

    /**
     * Add format and currency to price
     * @param $type
     * @param $value
     * @param $valueType
     */
    protected function preparePriceValues($type, $value, $valueType)
    {
        $store = $this->storeManager->getStore();
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

        if (isset($type)) {
            if ($type == self::COMMISSION_TYPE_FIXED) {
                $this->setData($valueType, $currency->toCurrency(sprintf("%f", $value)));
            } else {
                $this->setData($valueType, number_format($this->getData($valueType), 2) . '%');
            }
        }
    }

    /**
     * @param $order
     * @param string $status
     */
    public function addTransaction($order, $status = Transaction::STATUS_PENDING)
    {
        $this->_registry->register(\Amasty\Affiliate\Model\RegistryConstants::CURRENT_ORDER, $order, true);

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->getByOrderProgramIds(
            $order->getIncrementId(),
            $this->getProgramId()
        );

        $type = Transaction::TYPE_PER_SALE;
        $onHoldPeriod = $this->scopeConfig->getValue('amasty_affiliate/commission/holding_period');
        if ($transaction->getTransactionId()) {//changing of existing transaction
            if ($onHoldPeriod <= 0) {
                $transaction->complete();
            } else {
                $transaction->onHold();
            }
        } else {//after place order, new transaction; not create for coupons transactions
            $isAffiliateCoupon = false;
            if ($order->getCouponCode() && $this->couponCollection->isAffiliateCoupon($order->getCouponCode())) {
                $isAffiliateCoupon = true;
            }
            if (!$this->transactionsCollectionFactory->create()->isOrderTransactionExists($order->getIncrementId())
                || !$isAffiliateCoupon
            ) {
                if ($this->getWithdrawalType() == Transaction::TYPE_PER_PROFIT) {
                    $type = Transaction::TYPE_FOR_FUTURE_PER_PROFIT;
                }
                $transaction->newTransaction($this, $order, $status, $type);
            }
        }
    }

    /**
     * Getter for rule combine conditions instance
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Prepare program's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * @return array
     */
    public function getAvailableCommissionTypes()
    {
        return [self::COMMISSION_TYPE_PERCENT => __('Percent'), self::COMMISSION_TYPE_FIXED => __('Fixed')];
    }

    /**
     * @return array
     */
    public function getAvailableWithdrawalTypes()
    {
        return [Transaction::TYPE_PER_PROFIT => __('Per Profit'), Transaction::TYPE_PER_SALE => __('Per Sale')];
    }

    /**
     * @return int|null
     */
    public function getProgramId()
    {
        return $this->_getData(ProgramInterface::PROGRAM_ID);
    }

    /**
     * @param int $programId
     * @return ProgramInterface
     */
    public function setProgramId($programId)
    {
        $this->setData(ProgramInterface::PROGRAM_ID, $programId);

        return $this;
    }

    /**
     * @return int|mixed|null
     */
    public function getRuleId()
    {
        return $this->_getData(ProgramInterface::RULE_ID);
    }

    /**
     * @param int|null $ruleId
     * @return ProgramInterface
     */
    public function setRuleId($ruleId)
    {
        $this->setData(ProgramInterface::RULE_ID, $ruleId);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->_getData(ProgramInterface::NAME);
    }

    /**
     * @param string|null $name
     * @return ProgramInterface
     */
    public function setName($name)
    {
        $this->setData(ProgramInterface::NAME, $name);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWithdrawalType()
    {
        return $this->_getData(ProgramInterface::WITHDRAWAL_TYPE);
    }

    /**
     * @param string $withdrawalType
     * @return ProgramInterface
     */
    public function setWithdrawalType($withdrawalType)
    {
        $this->setData(ProgramInterface::WITHDRAWAL_TYPE, $withdrawalType);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsActive()
    {
        return $this->_getData(ProgramInterface::IS_ACTIVE);
    }

    /**
     * @param int $isActive
     * @return ProgramInterface
     */
    public function setIsActive($isActive)
    {
        $this->setData(ProgramInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCommissionValue()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE);
    }

    /**
     * @param float|null $commissionValue
     * @return ProgramInterface
     */
    public function setCommissionValue($commissionValue)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE, $commissionValue);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRestrictTransactionsToNumberOrders()
    {
        return $this->_getData(ProgramInterface::RESTRICT_TRANSACTIONS_TO_NUMBER_ORDERS);
    }

    /**
     * @param int|null $restrictTransactionsToNumberOrders
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setRestrictTransactionsToNumberOrders($restrictTransactionsToNumberOrders)
    {
        $this->setData(ProgramInterface::RESTRICT_TRANSACTIONS_TO_NUMBER_ORDERS, $restrictTransactionsToNumberOrders);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCommissionPerProfitAmount()
    {
        return $this->_getData(ProgramInterface::COMMISSION_PER_PROFIT_AMOUNT);
    }

    /**
     * @param float|null $commissionPerProfitAmount
     * @return ProgramInterface
     */
    public function setCommissionPerProfitAmount($commissionPerProfitAmount)
    {
        $this->setData(ProgramInterface::COMMISSION_PER_PROFIT_AMOUNT, $commissionPerProfitAmount);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommissionValueType()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE_TYPE);
    }

    /**
     * @param string|null $commissionValueType
     * @return ProgramInterface
     */
    public function setCommissionValueType($commissionValueType)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE_TYPE, $commissionValueType);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFromSecondOrder()
    {
        return $this->_getData(ProgramInterface::FROM_SECOND_ORDER);
    }

    /**
     * @param int $fromSecondOrder
     * @return ProgramInterface
     */
    public function setFromSecondOrder($fromSecondOrder)
    {
        $this->setData(ProgramInterface::FROM_SECOND_ORDER, $fromSecondOrder);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommissionTypeSecond()
    {
        return $this->_getData(ProgramInterface::COMMISSION_TYPE_SECOND);
    }

    /**
     * @param string|null $commissionTypeSecond
     * @return ProgramInterface
     */
    public function setCommissionTypeSecond($commissionTypeSecond)
    {
        $this->setData(ProgramInterface::COMMISSION_TYPE_SECOND, $commissionTypeSecond);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCommissionValueSecond()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE_SECOND);
    }

    /**
     * @param float|null $commissionValueSecond
     * @return ProgramInterface
     */
    public function setCommissionValueSecond($commissionValueSecond)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE_SECOND, $commissionValueSecond);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsLifetime()
    {
        return $this->_getData(ProgramInterface::IS_LIFETIME);
    }

    /**
     * @param int $isLifetime
     * @return ProgramInterface
     */
    public function setIsLifetime($isLifetime)
    {
        $this->setData(ProgramInterface::IS_LIFETIME, $isLifetime);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrequency()
    {
        return $this->_getData(ProgramInterface::FREQUENCY);
    }

    /**
     * @param string|null $frequency
     * @return ProgramInterface
     */
    public function setFrequency($frequency)
    {
        $this->setData(ProgramInterface::FREQUENCY, $frequency);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalSales()
    {
        return $this->_getData(ProgramInterface::TOTAL_SALES);
    }

    /**
     * @param float|null $totalSales
     * @return ProgramInterface
     */
    public function setTotalSales($totalSales)
    {
        $this->setData(ProgramInterface::TOTAL_SALES, $totalSales);

        return $this;
    }

    /**
     * @return mixed|string|null
     */
    public function getAvailableCustomers()
    {
        return $this->_getData(ProgramInterface::AVAILABLE_CUSTOMERS);
    }

    /**
     * @param string|null $customers
     * @return ProgramInterface
     */
    public function setAvailableCustomers($customers)
    {
        $this->setData(ProgramInterface::AVAILABLE_CUSTOMERS, $customers);

        return $this;
    }

    /**
     * @return mixed|string|null
     */
    public function getAvailableGroups()
    {
        return $this->_getData(ProgramInterface::AVAILABLE_GROUPS);
    }

    /**
     * @param string|null $groups
     * @return ProgramInterface
     */
    public function setAvailableGroups($groups)
    {
        $this->setData(ProgramInterface::AVAILABLE_GROUPS, $groups);

        return $this;
    }

    /**
     * @return ProgramCommissionCalculationInterface|null
     */
    public function getCommissionCalculation()
    {
        return $this->_getData(ProgramInterface::COMMISSION_CALCULATION);
    }

    /**
     * @param ProgramCommissionCalculationInterface $commissionCalculation
     * @return ProgramInterface
     */
    public function setCommissionCalculation(ProgramCommissionCalculationInterface $commissionCalculation)
    {
        return $this->setData(ProgramInterface::COMMISSION_CALCULATION, $commissionCalculation);
    }
}
