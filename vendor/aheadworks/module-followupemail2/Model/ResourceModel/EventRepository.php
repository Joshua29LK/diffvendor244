<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Collection as EventCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class EventRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EventInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var EventSearchResultsInterfaceFactory
     */
    private $eventSearchResultsFactory;

    /**
     * @var EventCollectionFactory
     */
    private $eventCollectionFactory;

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
     * @param EventInterfaceFactory $eventFactory
     * @param EventSearchResultsInterfaceFactory $eventSearchResultsFactory
     * @param EventCollectionFactory $eventCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        EventInterfaceFactory $eventFactory,
        EventSearchResultsInterfaceFactory $eventSearchResultsFactory,
        EventCollectionFactory $eventCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->eventFactory = $eventFactory;
        $this->eventSearchResultsFactory = $eventSearchResultsFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EventInterface $event)
    {
        try {
            $this->entityManager->save($event);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$event->getId()]);
        return $this->get($event->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($eventId)
    {
        if (!isset($this->instances[$eventId])) {
            /** @var EventInterface $event */
            $event = $this->eventFactory->create();
            $this->entityManager->load($event, $eventId);
            if (!$event->getId()) {
                throw NoSuchEntityException::singleField('id', $eventId);
            }
            $this->instances[$eventId] = $event;
        }
        return $this->instances[$eventId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EventSearchResultsInterface $searchResults */
        $searchResults = $this->eventSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var EventCollection $collection */
        $collection = $this->eventCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, EventInterface::class);

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

        $events = [];
        /** @var \Aheadworks\Followupemail2\Model\Event $eventModel */
        foreach ($collection as $eventModel) {
            /** @var EventInterface $event */
            $event = $this->eventFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $event,
                $eventModel->getData(),
                EventInterface::class
            );
            $events[] = $event;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($events)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EventInterface $event)
    {
        return $this->deleteById($event->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($eventId)
    {
        /** @var EventInterface $event */
        $event = $this->eventFactory->create();
        $this->entityManager->load($event, $eventId);
        if (!$event->getId()) {
            throw NoSuchEntityException::singleField('id', $eventId);
        }
        $this->entityManager->delete($event);
        unset($this->instances[$eventId]);
        return true;
    }
}
