<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\CodeGenerator;
use Aheadworks\Followupemail2\Model\Serializer;
use Aheadworks\Followupemail2\Model\Unsubscribe\Service as UnsubscribeService;
use Aheadworks\Followupemail2\Model\Event\Queue\ItemProcessor as EventQueueItemProcessor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class QueueManagement
 * @package Aheadworks\Followupemail2\Model\Event
 */
class QueueManagement implements EventQueueManagementInterface
{
    /**
     * @var EventQueueInterfaceFactory
     */
    private $eventQueueFactory;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @var CodeGenerator
     */
    private $codeGenerator;

    /**
     * @var UnsubscribeService
     */
    private $unsubscribeService;

    /**
     * @var EventQueueItemProcessor
     */
    private $eventQueueItemProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EventQueueInterfaceFactory $eventQueueFactory
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param EventManagementInterface $eventManagement
     * @param CodeGenerator $codeGenerator
     * @param UnsubscribeService $unsubscribeService
     * @param EventQueueItemProcessor $eventQueueItemProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        EventQueueInterfaceFactory $eventQueueFactory,
        EventQueueRepositoryInterface $eventQueueRepository,
        EventManagementInterface $eventManagement,
        CodeGenerator $codeGenerator,
        UnsubscribeService $unsubscribeService,
        EventQueueItemProcessor $eventQueueItemProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer
    ) {
        $this->eventQueueFactory = $eventQueueFactory;
        $this->eventQueueRepository = $eventQueueRepository;
        $this->eventManagement = $eventManagement;
        $this->codeGenerator = $codeGenerator;
        $this->unsubscribeService = $unsubscribeService;
        $this->eventQueueItemProcessor = $eventQueueItemProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEvents($eventCode, $referenceId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::EVENT_TYPE, $eventCode)
            ->addFilter(EventQueueInterface::REFERENCE_ID, $referenceId)
            ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq');

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        /** @var EventQueueInterface $eventQueueItem */
        foreach ($result->getItems() as $eventQueueItem) {
            $this->cancelEventQueueItem($eventQueueItem->getId());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEventsByCampaignId($campaignId)
    {
        /** @var EventInterface[] $events */
        $events = $this->eventManagement->getEventsByCampaignId($campaignId);

        $eventIds = [];
        /** @var EventInterface $event */
        foreach ($events as $event) {
            $eventIds[] = $event->getId();
        }

        if (count($eventIds) > 0) {
            $this->searchCriteriaBuilder
                ->addFilter(EventQueueInterface::EVENT_ID, $eventIds, 'in')
                ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq');

            /** @var EventQueueSearchResultsInterface $result */
            $result = $this->eventQueueRepository->getList(
                $this->searchCriteriaBuilder->create()
            );

            /** @var EventQueueInterface $eventQueueItem */
            foreach ($result->getItems() as $eventQueueItem) {
                $this->cancelEventQueueItem($eventQueueItem->getId());
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEventsByEventId($eventId, $referenceId = null)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::EVENT_ID, $eventId, 'eq')
            ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq');

        if ($referenceId) {
            $this->searchCriteriaBuilder
                ->addFilter(EventQueueInterface::REFERENCE_ID, $referenceId);
        }

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        /** @var EventQueueInterface $eventQueueItem */
        foreach ($result->getItems() as $eventQueueItem) {
            $this->cancelEventQueueItem($eventQueueItem->getId());
        }

        return true;
    }

    /**
     * Cancel event queue item
     *
     * @param int $eventQueueId
     * @return bool
     */
    private function cancelEventQueueItem($eventQueueId)
    {
        try {
            $eventQueueItem = $this->eventQueueRepository->get($eventQueueId);
            if (count($eventQueueItem->getEmails()) > 0) {
                $eventQueueItem->setStatus(EventQueueInterface::STATUS_CANCELLED);
                $this->eventQueueRepository->save($eventQueueItem);
            } else {
                $this->eventQueueRepository->delete($eventQueueItem);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelScheduledEmail($eventQueueId)
    {
        try {
            $eventQueueItem = $this->eventQueueRepository->get($eventQueueId);
            if ($eventQueueItem->getStatus() == EventQueueInterface::STATUS_PROCESSING) {
                $this->eventQueueItemProcessor->cancelScheduledEmail($eventQueueItem);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The email can not be cancelled.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEvent($eventQueueId)
    {
        try {
            $eventQueueItem = $this->eventQueueRepository->get($eventQueueId);
            if ($eventQueueItem->getStatus() == EventQueueInterface::STATUS_PROCESSING) {
                $this->cancelEventQueueItem($eventQueueId);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The email chain can not be cancelled.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendNextScheduledEmail($eventQueueId)
    {
        try {
            $eventQueueItem = $this->eventQueueRepository->get($eventQueueId);
            if ($eventQueueItem->getStatus() == EventQueueInterface::STATUS_PROCESSING) {
                $this->eventQueueItemProcessor->sendNextScheduledEmail($eventQueueItem);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The email can not be sent.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledEmailPreview($eventQueueItem)
    {
        try {
            /** @var PreviewInterface|false $preview */
            $preview =  $this->eventQueueItemProcessor->getScheduledEmailPreview($eventQueueItem);
        } catch (\Exception $e) {
            $preview = false;
        }

        if (!$preview) {
            throw new LocalizedException(__('Email preview can not be created.'));
        }

        return $preview;
    }

    /**
     * {@inheritdoc}
     */
    public function add(
        EventInterface $event,
        EventHistoryInterface $eventHistoryItem,
        $preventToAddDuplicateEmails = true
    ) {
        $eventData = $this->serializer->unserialize($eventHistoryItem->getEventData());
        $storeId = isset($eventData['store_id']) ? $eventData['store_id'] : 0;
        $email = $eventData['email'];

        if ($this->unsubscribeService->isUnsubscribed($event->getId(), $email, $storeId)) {
            return false;
        }

        if ($preventToAddDuplicateEmails) {
            $this->searchCriteriaBuilder
                ->addFilter(EventQueueInterface::EVENT_ID, $event->getId())
                ->addFilter(EventQueueInterface::REFERENCE_ID, $eventHistoryItem->getReferenceId());

            /** @var EventQueueSearchResultsInterface $result */
            $result = $this->eventQueueRepository->getList(
                $this->searchCriteriaBuilder->create()
            );

            foreach ($result->getItems() as $eventQueueItem) {
                if (count($eventQueueItem->getEmails()) > 0) {
                    // prevent to add duplicate emails
                    return false;
                }
            }
        }

        /** @var EventQueueInterface $eventQueueItem */
        $eventQueueItem = $this->eventQueueFactory->create();
        $securityCode = $this->codeGenerator->getCode();
        $eventQueueItem
            ->setEventId($event->getId())
            ->setReferenceId($eventHistoryItem->getReferenceId())
            ->setEventType($eventHistoryItem->getEventType())
            ->setEventData($eventHistoryItem->getEventData())
            ->setSecurityCode($securityCode)
            ->setStatus(EventQueueInterface::STATUS_PROCESSING);

        try {
            $eventQueueItem = $this->eventQueueRepository->save($eventQueueItem);
        } catch (\Exception $e) {
            return false;
        }

        return $eventQueueItem;
    }

    /**
     * {@inheritdoc}
     */
    public function processUnprocessedItems($maxItemsCount)
    {
        $countQueuedEmails = 0;
        $allProcessed = false;
        $page = 1;
        $itemsCount = 0;

        while ($countQueuedEmails < $maxItemsCount && !$allProcessed) {
            $this->searchCriteriaBuilder
                ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING)
                ->setPageSize($maxItemsCount)
                ->setCurrentPage($page);

            /** @var EventQueueSearchResultsInterface $result */
            $result = $this->eventQueueRepository->getList(
                $this->searchCriteriaBuilder->create()
            );

            if (count($result->getItems()) == 0) {
                $allProcessed = true;
            }

            /** @var EventQueueInterface $eventQueueItem */
            foreach ($result->getItems() as $eventQueueItem) {
                if ($this->eventQueueItemProcessor->process($eventQueueItem)) {
                    $countQueuedEmails++;
                    if ($countQueuedEmails >= $maxItemsCount) {
                        $allProcessed = true;
                        break;
                    }
                }
                $itemsCount++;
                if ($itemsCount >= $result->getTotalCount()) {
                    $allProcessed = true;
                    break;
                }
            }
            $page++;
        }

        return true;
    }
}
