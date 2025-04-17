<?php

namespace Amasty\Affiliate\Model\ResourceModel\Account\Transaction\Grid;

use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction\Collection as TransactionCollection;
use Amasty\Affiliate\Model\Transaction;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * @method Transaction[] getItems()
 */
class Collection extends TransactionCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->request = $request;
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

    /**
     * Init select
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter(
            'main_table.' . TransactionInterface::STATUS,
            ['neq' => Transaction::STATUS_ORDER_LIMIT_EXCEEDED]
        );

        return $this;
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }
}
