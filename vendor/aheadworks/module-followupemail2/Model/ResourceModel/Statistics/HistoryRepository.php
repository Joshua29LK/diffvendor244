<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Statistics;

use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistorySearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistorySearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\StatisticsHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\Collection as StatisticsHistoryCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\CollectionFactory
    as StatisticsHistoryCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class HistoryRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Statistics
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryRepository implements StatisticsHistoryRepositoryInterface
{
    /**
     * @var StatisticsHistoryInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var StatisticsHistoryInterfaceFactory
     */
    private $statisticsHistoryFactory;

    /**
     * @var StatisticsHistorySearchResultsInterfaceFactory
     */
    private $statisticsHistorySearchResultsFactory;

    /**
     * @var StatisticsHistoryCollectionFactory
     */
    private $statisticsHistoryCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param EntityManager $entityManager
     * @param StatisticsHistoryInterfaceFactory $statisticsHistoryFactory
     * @param StatisticsHistorySearchResultsInterfaceFactory $statisticsHistorySearchResultsFactory
     * @param StatisticsHistoryCollectionFactory $statisticsHistoryCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        StatisticsHistoryInterfaceFactory $statisticsHistoryFactory,
        StatisticsHistorySearchResultsInterfaceFactory $statisticsHistorySearchResultsFactory,
        StatisticsHistoryCollectionFactory $statisticsHistoryCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->statisticsHistoryFactory = $statisticsHistoryFactory;
        $this->statisticsHistorySearchResultsFactory = $statisticsHistorySearchResultsFactory;
        $this->statisticsHistoryCollectionFactory = $statisticsHistoryCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(StatisticsHistoryInterface $history)
    {
        try {
            $this->entityManager->save($history);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$history->getId()]);
        return $this->get($history->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($historyId)
    {
        if (!isset($this->instances[$historyId])) {
            /** @var StatisticsHistoryInterface $history */
            $history = $this->statisticsHistoryFactory->create();
            $this->entityManager->load($history, $historyId);
            if (!$history->getId()) {
                throw NoSuchEntityException::singleField('id', $historyId);
            }
            $this->instances[$historyId] = $history;
        }
        return $this->instances[$historyId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var StatisticsHistorySearchResultsInterface $searchResults */
        $searchResults = $this->statisticsHistorySearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var StatisticsHistoryCollection $collection */
        $collection = $this->statisticsHistoryCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, StatisticsHistoryInterface::class);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }

        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $histories = [];
        /** @var \Aheadworks\Followupemail2\Model\StatisticsHistory $historyModel */
        foreach ($collection as $historyModel) {
            /** @var StatisticsHistoryInterface $history */
            $history = $this->statisticsHistoryFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $history,
                $historyModel->getData(),
                StatisticsHistoryInterface::class
            );
            $histories[] = $history;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($histories)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(StatisticsHistoryInterface $history)
    {
        return $this->deleteById($history->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($historyId)
    {
        /** @var StatisticsHistoryInterface $history */
        $history = $this->statisticsHistoryFactory->create();
        $this->entityManager->load($history, $historyId);
        if (!$history->getId()) {
            throw NoSuchEntityException::singleField('id', $historyId);
        }
        $this->entityManager->delete($history);
        unset($this->instances[$historyId]);
        return true;
    }
}
