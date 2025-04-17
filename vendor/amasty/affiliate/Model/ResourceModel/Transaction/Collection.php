<?php

namespace Amasty\Affiliate\Model\ResourceModel\Transaction;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction;
use Amasty\Affiliate\Model\Transaction as TransactionModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    public const GT_EXCL_TAX_EXPR = 'GREATEST((sales_order.base_grand_total) - '
    . 'COALESCE(SUM(oi.base_weee_tax_applied_row_amnt), 0) - (sales_order.base_tax_amount), 0)';

    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'status' => 'main_table.status'
        ]
    ];

    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        AccountRepositoryInterface $accountRepository,
        Session $customerSession,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->accountRepository = $accountRepository;
        $this->customerSession = $customerSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            TransactionModel::class,
            Transaction::class
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['sales_order' => $this->getTable('sales_order')],
                'main_table.order_increment_id = sales_order.increment_id',
                [
                    'customer_account_id' => 'customer_id',
                    'order_id' => 'entity_id',
                    'increment_id', 'customer_email', 'store_id',
                    'base_subtotal', 'base_tax_amount',
                    'base_grand_total', 'total_qty_ordered'
                ]
            )->joinLeft(
                ['affiliate_account' => $this->getTable('amasty_affiliate_account')],
                'main_table.affiliate_account_id = affiliate_account.account_id',
                ['customer_id']
            )->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = affiliate_account.customer_id',
                ['email', 'firstname', 'lastname']
            )->distinct();

        return $this;
    }

    public function addGrandTotalExclTaxToSelect()
    {
        $this->getSelect()->joinLeft(
            ['oi' => $this->getTable('sales_order_item')],
            'sales_order.entity_id = oi.order_id',
            ['gt_excl_tax' => self::GT_EXCL_TAX_EXPR]
        )->group(['order_id', 'main_table.transaction_id']);

        return $this;
    }

    public function addFilterByOrderIncrementId($incrementId)
    {
        $this->addFieldToFilter('order_increment_id', ['eq' => $incrementId]);

        return $this;
    }

    public function isOrderTransactionExists($incrementId)
    {
        $isOrderTransactionExists = false;

        if ($this->addFilterByOrderIncrementId($incrementId)->getSize() > 0) {
            $isOrderTransactionExists = true;
        }

        return $isOrderTransactionExists;
    }

    /**
     * @param $customerEmail
     * @param $programId
     * @return $this
     */
    public function addCustomerProgramFilter($customerEmail, $programId)
    {
        $this->addFieldToFilter('customer_email', $customerEmail);
        $this->addFieldToFilter('program_id', $programId);

        return $this;
    }

    /**
     * @return $this
     */
    public function addHoldFilter()
    {
        $this->addFieldToFilter('main_table.status', ['eq' => TransactionModel::STATUS_ON_HOLD]);
        $onHoldDays = $this->scopeConfig->getValue('amasty_affiliate/commission/holding_period');
        $this->getSelect()->where('main_table.updated_at < NOW() - INTERVAL ? DAY', $onHoldDays);

        return $this;
    }

    /**
     * @param TransactionModel $transaction
     * @return TransactionModel
     */
    public function getPerProfitTransaction($transaction)
    {
        $this
            ->addFieldToFilter('main_table.updated_at', ['gt' => $transaction->getUpdatedAt()])
            ->addFieldToFilter('main_table.type', ['eq' => $transaction::TYPE_PER_PROFIT])
            ->addFieldToFilter('main_table.affiliate_account_id', ['eq' => $transaction->getAffiliateAccountId()])
            ->addFieldToFilter('main_table.program_id', ['eq' => $transaction->getProgramId()])
            ->setOrder('main_table.updated_at', 'ASC');

        return $this->getFirstItem();
    }

    /**
     * @return mixed
     */
    public function getProfit()
    {
        $this->getSelect()->columns(['subtotal' => 'SUM(base_subtotal)', 'discount' => 'SUM(base_discount_amount)']);

        $subtotal = $this->getFirstItem()->getSubtotal();
        $discount = $this->getFirstItem()->getDiscount();
        $profit = $subtotal + $discount;

        return $profit;
    }

    /**
     * @param $programId
     * @param $accountId
     * @return $this
     */
    public function addForFutureFilter($programId, $accountId)
    {
        $this->addFieldToFilter('program_id', ['eq' => $programId]);
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);
        $this->addFieldToFilter('type', ['eq' => TransactionModel::TYPE_FOR_FUTURE_PER_PROFIT]);
        $this->addFieldToFilter(
            'main_table.status',
            ['eq' => TransactionModel::STATUS_READY_FOR_PER_PROFIT]
        );

        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->getConnection()->update(
            $this->getResource()->getMainTable(),
            ['status' => $status]
        );

        return $this;
    }

    /**
     * @param $accountId
     * @return $this
     */
    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);

        return $this;
    }

    public function addIncrementIdFilter($incrementId)
    {
        $this->addFieldToFilter('order_increment_id', ['eq' => $incrementId]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addFrontTypeFilter()
    {
        $this->addFieldToFilter(
            'type',
            ['nin' =>
                [
                    TransactionModel::TYPE_FOR_FUTURE_PER_PROFIT
                ],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addCompletedFilter()
    {
        $this->addFieldToFilter('main_table.status', ['eq' => TransactionModel::STATUS_COMPLETED]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addAscSorting()
    {
        return $this->setOrder('updated_at', 'ASC');
    }

    /**
     * @return $this
     */
    public function addDescSorting()
    {
        return $this->setOrder('updated_at', 'DESC');
    }

    public function addTypeFilter($type)
    {
        return $this->addFieldToFilter('type', ['eq' => $type]);
    }

    /**
     * @return Collection
     */
    public function removeZeroComissionTransactions()
    {
        return $this->addFieldToFilter(
            [
                'main_table.commission',
                'main_table.status'
            ],
            [
                ['neq' => 0],
                ['er' => TransactionModel::STATUS_CANCELED]
            ]
        );
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->getSelect()->getPart(Select::HAVING)) {
            $countSelect->reset();
            $group = $this->getSelect()->getPart(Select::GROUP);
            $countSelect->from(
                ['main_table' => $this->getSelect()],
                [new \Zend_Db_Expr("COUNT(DISTINCT " . implode(", ", $group) . ")")]
            );
        }

        return $countSelect;
    }
}
