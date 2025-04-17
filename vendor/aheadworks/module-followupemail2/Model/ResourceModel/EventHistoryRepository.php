<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory\Collection as EventHistoryCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory\CollectionFactory as EventHistoryCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class EventHistoryRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventHistoryRepository implements EventHistoryRepositoryInterface
{
    /**
     * @var EventHistoryInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventHistoryInterfaceFactory
     */
    private $eventHistoryFactory;

    /**
     * @var EventHistorySearchResultsInterfaceFactory
     */
    private $eventHistorySearchResultsFactory;

    /**
     * @var EventHistoryCollectionFactory
     */
    private $eventHistoryCollectionFactory;

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
     * @param EventHistoryInterfaceFactory $eventHistoryFactory
     * @param EventHistorySearchResultsInterfaceFactory $eventHistorySearchResultsFactory
     * @param EventHistoryCollectionFactory $eventHistoryCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        EventHistoryInterfaceFactory $eventHistoryFactory,
        EventHistorySearchResultsInterfaceFactory $eventHistorySearchResultsFactory,
        EventHistoryCollectionFactory $eventHistoryCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->eventHistoryFactory = $eventHistoryFactory;
        $this->eventHistorySearchResultsFactory = $eventHistorySearchResultsFactory;
        $this->eventHistoryCollectionFactory = $eventHistoryCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EventHistoryInterface $eventHistory)
    {
        try {
            $this->entityManager->save($eventHistory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$eventHistory->getId()]);
        return $this->get($eventHistory->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($eventHistoryId)
    {
        if (!isset($this->instances[$eventHistoryId])) {
            /** @var EventHistoryInterface $eventHistory */
            $eventHistory = $this->eventHistoryFactory->create();
            $this->entityManager->load($eventHistory, $eventHistoryId);
            if (!$eventHistory->getId()) {
                throw NoSuchEntityException::singleField('id', $eventHistoryId);
            }
            $this->instances[$eventHistoryId] = $eventHistory;
        }
        return $this->instances[$eventHistoryId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EventHistorySearchResultsInterface $searchResults */
        $searchResults = $this->eventHistorySearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var EventHistoryCollection $collection */
        $collection = $this->eventHistoryCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, EventHistoryInterface::class);

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

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $eventHistories = [];
        /** @var \Aheadworks\Followupemail2\Model\EventHistory $eventHistoryModel */
        foreach ($collection as $eventHistoryModel) {
            /** @var EventHistoryInterface $eventHistory */
            $eventHistory = $this->eventHistoryFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $eventHistory,
                $eventHistoryModel->getData(),
                EventHistoryInterface::class
            );
            $eventHistories[] = $eventHistory;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($eventHistories)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EventHistoryInterface $eventHistory)
    {
        return $this->deleteById($eventHistory->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($eventHistoryId)
    {
        /** @var EventHistoryInterface $eventHistory */
        $eventHistory = $this->eventHistoryFactory->create();
        $this->entityManager->load($eventHistory, $eventHistoryId);
        if (!$eventHistory->getId()) {
            throw NoSuchEntityException::singleField('id', $eventHistoryId);
        }
        $this->entityManager->delete($eventHistory);
        unset($this->instances[$eventHistoryId]);
        return true;
    }
}
