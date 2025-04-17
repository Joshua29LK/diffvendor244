<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class EventHistoryManagement
 * @package Aheadworks\Followupemail2\Model
 */
class EventHistoryManagement implements EventHistoryManagementInterface
{
    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var EventHistoryInterfaceFactory
     */
    private $eventHistoryFactory;

    /**
     * @var EventHistoryRepositoryInterface
     */
    private $eventHistoryRepository;

    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EventTypePool $eventTypePool
     * @param EventHistoryInterfaceFactory $eventHistoryFactory
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     */
    public function __construct(
        EventTypePool $eventTypePool,
        EventHistoryInterfaceFactory $eventHistoryFactory,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->eventTypePool = $eventTypePool;
        $this->eventHistoryFactory = $eventHistoryFactory;
        $this->eventHistoryRepository = $eventHistoryRepository;
        $this->eventQueueManagement = $eventQueueManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($eventCode, $eventData)
    {
        /** @var EventTypeInterface $eventType */
        $eventType = $this->eventTypePool->getType($eventCode);
        /** @var HandlerInterface $eventHandler */
        $eventHandler = $eventType->getHandler();

        if ($eventHandler && $eventHandler->validateEventData($eventData)) {
            $eventHandler->cancelEvents($eventCode, $eventData);

            $this->searchCriteriaBuilder
                ->addFilter(EventHistoryInterface::REFERENCE_ID, $eventData[$eventHandler->getReferenceDataKey()])
                ->addFilter(EventHistoryInterface::EVENT_TYPE, $eventHandler->getType());

            /** @var EventHistorySearchResultsInterface $result */
            $result = $this->eventHistoryRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            /** @var EventHistoryInterface[] $eventHistoryItems */
            $eventHistoryItems = $result->getItems();
            /** @var EventHistoryInterface $eventHistoryItem */
            $eventHistoryItem = reset($eventHistoryItems);

            if ($eventHistoryItem) {
                if ($eventHistoryItem->getProcessed()) {
                    $this->eventHistoryRepository->delete($eventHistoryItem);

                    /** @var EventHistoryInterface $eventHistory */
                    $eventHistoryItem = $this->eventHistoryFactory->create();
                }
            } else {
                /** @var EventHistoryInterface $eventHistoryItem */
                $eventHistoryItem = $this->eventHistoryFactory->create();
            }

            $eventHistoryItem
                ->setReferenceId($eventData[$eventHandler->getReferenceDataKey()])
                ->setEventType($eventCode)
                ->setEventData($this->getPreparedEventData($eventData))
                ->setProcessed(false);

            $this->eventHistoryRepository->save($eventHistoryItem);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function processUnprocessedItems($maxItemsCount)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventHistoryInterface::PROCESSED, false)
            ->setPageSize($maxItemsCount);

        /** @var EventHistorySearchResultsInterface $result */
        $result = $this->eventHistoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
        $eventHistoryItems = $result->getItems();
        foreach ($eventHistoryItems as $eventHistoryItem) {
            try {
                /** @var EventTypeInterface $eventType */
                $eventType = $this->eventTypePool->getType($eventHistoryItem->getEventType());
                /** @var HandlerInterface $eventHandler */
                $eventHandler = $eventType->getHandler();
                $eventHandler->process($eventHistoryItem);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Get prepared event data
     *
     * @param array $data
     * @return string
     */
    private function getPreparedEventData(array $data)
    {
        foreach ($data as $key => $value) {
            if ((is_array($value) || is_object($value))) {
                unset($data[$key]);
            }

            if (isset($data[$key]) && preg_match("/\r\n|\r|\n/", $value ?? '')) {
                $data[$key] = preg_replace("/\r\n|\r|\n/", "", $value);
            }
        }
        return $this->serializer->serialize($data);
    }
}
