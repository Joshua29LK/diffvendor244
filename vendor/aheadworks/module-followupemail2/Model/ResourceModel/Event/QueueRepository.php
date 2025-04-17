<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Collection as EventQueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\CollectionFactory as EventQueueCollectionFactory;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Processor as ScheduledEmailsIndexer;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class QueueRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueRepository implements EventQueueRepositoryInterface
{
    /**
     * @var EventQueueInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventQueueInterfaceFactory
     */
    private $eventQueueFactory;

    /**
     * @var EventQueueSearchResultsInterfaceFactory
     */
    private $eventQueueSearchResultsFactory;

    /**
     * @var EventQueueCollectionFactory
     */
    private $eventQueueCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ScheduledEmailsIndexer
     */
    private $scheduledEmailsIndexer;

    /**
     * @param EntityManager $entityManager
     * @param EventQueueInterfaceFactory $eventQueueFactory
     * @param EventQueueSearchResultsInterfaceFactory $eventQueueSearchResultsFactory
     * @param EventQueueCollectionFactory $eventQueueCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ScheduledEmailsIndexer $scheduledEmailsIndexer
     */
    public function __construct(
        EntityManager $entityManager,
        EventQueueInterfaceFactory $eventQueueFactory,
        EventQueueSearchResultsInterfaceFactory $eventQueueSearchResultsFactory,
        EventQueueCollectionFactory $eventQueueCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper,
        ScheduledEmailsIndexer $scheduledEmailsIndexer
    ) {
        $this->entityManager = $entityManager;
        $this->eventQueueFactory = $eventQueueFactory;
        $this->eventQueueSearchResultsFactory = $eventQueueSearchResultsFactory;
        $this->eventQueueCollectionFactory = $eventQueueCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->scheduledEmailsIndexer = $scheduledEmailsIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EventQueueInterface $eventQueue)
    {
        try {
            $this->entityManager->save($eventQueue);
            $this->scheduledEmailsIndexer->reindexRow($eventQueue->getId());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$eventQueue->getId()]);
        return $this->get($eventQueue->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($eventQueueId)
    {
        if (!isset($this->instances[$eventQueueId])) {
            /** @var EventQueueInterface $eventQueue */
            $eventQueue = $this->eventQueueFactory->create();
            $this->entityManager->load($eventQueue, $eventQueueId);
            if (!$eventQueue->getId()) {
                throw NoSuchEntityException::singleField('id', $eventQueueId);
            }
            $this->instances[$eventQueueId] = $eventQueue;
        }
        return $this->instances[$eventQueueId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EventQueueSearchResultsInterface $searchResults */
        $searchResults = $this->eventQueueSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var EventQueueCollection $collection */
        $collection = $this->eventQueueCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, EventQueueInterface::class);

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

        $eventQueueItems = [];
        /** @var \Aheadworks\Followupemail2\Model\Event\Queue $eventQueueModel */
        foreach ($collection as $eventQueueModel) {
            /** @var EventQueueInterface $eventQueue */
            $eventQueue = $this->eventQueueFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $eventQueue,
                $eventQueueModel->getData(),
                EventQueueInterface::class
            );
            $eventQueueItems[] = $eventQueue;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($eventQueueItems)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EventQueueInterface $eventQueue)
    {
        return $this->deleteById($eventQueue->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($eventQueueId)
    {
        /** @var EventQueueInterface $eventQueue */
        $eventQueue = $this->eventQueueFactory->create();
        $this->entityManager->load($eventQueue, $eventQueueId);
        if (!$eventQueue->getId()) {
            throw NoSuchEntityException::singleField('id', $eventQueueId);
        }
        $this->entityManager->delete($eventQueue);
        unset($this->instances[$eventQueueId]);
        return true;
    }
}
