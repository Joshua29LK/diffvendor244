<?php

namespace Amasty\Affiliate\Model\ResourceModel\Transaction\Grid;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Model\Transaction;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction\Collection as TransactionCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

/**
 * @method Transaction[] getItems()
 */
class Collection extends TransactionCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        Session $customerSession,
        $eventObject,
        $resourceModel,
        ScopeConfigInterface $scopeConfig,
        AccountRepositoryInterface $accountRepository,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $scopeConfig,
            $accountRepository,
            $customerSession,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    protected function _initSelect()
    {
        $select = parent::_initSelect()->getSelect();
        $this->removeZeroComissionTransactions();
        $this->addGrandTotalExclTaxToSelect();
        $select->where('main_table.type <> ?', Transaction::TYPE_FOR_FUTURE_PER_PROFIT);
        $select->where('main_table.status <> ?', Transaction::STATUS_ORDER_LIMIT_EXCEEDED);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }
}
