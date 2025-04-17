<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\Validator as EventQueueValidator;
use Aheadworks\Followupemail2\Model\Serializer;
use Aheadworks\Followupemail2\Model\Unsubscribe\Service as UnsubscribeService;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor as EventQueueEmailProcessor;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler as EventQueueEmailScheduler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ItemProcessor
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class ItemProcessor
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var EventQueueValidator
     */
    private $eventQueueValidator;

    /**
     * @var UnsubscribeService
     */
    private $unsubscribeService;

    /**
     * @var EventQueueEmailProcessor
     */
    private $eventQueueEmailProcessor;

    /**
     * @var EventQueueEmailScheduler
     */
    private $eventQueueEmailScheduler;

    /**
     * @var EmailPreviewProcessor
     */
    private $emailPreviewProcessor;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param Validator $eventQueueValidator
     * @param UnsubscribeService $unsubscribeService
     * @param EmailProcessor $eventQueueEmailProcessor
     * @param EmailScheduler $eventQueueEmailScheduler
     * @param EmailPreviewProcessor $emailPreviewProcessor
     * @param Serializer $serializer
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventQueueRepositoryInterface $eventQueueRepository,
        EventQueueValidator $eventQueueValidator,
        UnsubscribeService $unsubscribeService,
        EventQueueEmailProcessor $eventQueueEmailProcessor,
        EventQueueEmailScheduler $eventQueueEmailScheduler,
        EmailPreviewProcessor $emailPreviewProcessor,
        Serializer $serializer
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventQueueRepository = $eventQueueRepository;
        $this->eventQueueValidator = $eventQueueValidator;
        $this->unsubscribeService = $unsubscribeService;
        $this->eventQueueEmailProcessor = $eventQueueEmailProcessor;
        $this->eventQueueEmailScheduler = $eventQueueEmailScheduler;
        $this->emailPreviewProcessor = $emailPreviewProcessor;
        $this->serializer = $serializer;
    }

    /**
     * Process event queue item, return true if an email is scheduled
     *
     * @param EventQueueInterface $eventQueueItem
     * @return bool
     */
    public function process($eventQueueItem)
    {
        try {
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($eventQueueItem->getEventId());

            if ($this->eventQueueValidator->isEventValid($event)
                && $this->isRecepientNotUnsubscribed($eventQueueItem)
            ) {
                /** @var EventQueueEmailInterface|null $lastEmail */
                $lastScheduledEmail = $this->eventQueueEmailProcessor->getLastScheduledEmail($eventQueueItem);
                if ($lastScheduledEmail) {
                    if ($this->eventQueueEmailProcessor->isPending($lastScheduledEmail)) {
                        return false;
                    }

                    $itemShouldBeCancelled = $this->eventQueueEmailProcessor->isEventQueueItemShouldBeCancelled(
                        $event,
                        $lastScheduledEmail
                    );
                    if ($itemShouldBeCancelled) {
                        $eventQueueItem->setStatus(EventQueueInterface::STATUS_CANCELLED);
                        $this->safeSaveEventQueueItem($eventQueueItem);
                        return false;
                    }
                }
                return $this->processItem($eventQueueItem);
            }

            $eventQueueItem->setStatus(EventQueueInterface::STATUS_CANCELLED);
            $this->safeSaveEventQueueItem($eventQueueItem);
        } catch (NoSuchEntityException $e) {
            $this->safeDeleteEventQueueItem($eventQueueItem);
        }

        return false;
    }

    /**
     * Process item
     *
     * @param EventQueueInterface $eventQueueItem
     * @return bool
     */
    private function processItem($eventQueueItem)
    {
        $emailsCount = count($eventQueueItem->getEmails());
        $eventQueueItem = $this->eventQueueEmailProcessor->process($eventQueueItem);
        $this->safeSaveEventQueueItem($eventQueueItem);

        if ($this->isNewEmailCreated($eventQueueItem, $emailsCount)) {
            return true;
        }

        return false;
    }

    /**
     * Check if new email created
     *
     * @param EventQueueInterface $eventQueueItem
     * @param int $prevEmailsCount
     * @return bool
     */
    private function isNewEmailCreated($eventQueueItem, $prevEmailsCount)
    {
        return count($eventQueueItem->getEmails()) > $prevEmailsCount;
    }

    /**
     * Safe save  event queue item
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueInterface
     */
    private function safeSaveEventQueueItem($eventQueueItem)
    {
        try {
            $this->eventQueueRepository->save($eventQueueItem);
        } catch (LocalizedException $e) {
            // do nothing
        }

        return $eventQueueItem;
    }

    /**
     * Safe delete event queue item
     *
     * @param $eventQueueItem
     * @return bool
     */
    private function safeDeleteEventQueueItem($eventQueueItem)
    {
        try {
            return $this->eventQueueRepository->delete($eventQueueItem);
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * Check if email recipient is not unsubscribed
     *
     * @param EventQueueInterface $eventQueueItem
     * @return bool
     *
     */
    private function isRecepientNotUnsubscribed(EventQueueInterface $eventQueueItem)
    {
        $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
        $storeId = isset($eventData['store_id']) ? $eventData['store_id'] : 0;
        $email = $eventData['email'];

        return !$this->unsubscribeService->isUnsubscribed($eventQueueItem->getEventId(), $email, $storeId);
    }

    /**
     * Cancel scheduled email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueInterface
     * @throws NoSuchEntityException
     */
    public function cancelScheduledEmail(EventQueueInterface $eventQueueItem)
    {
        /** @var EventInterface $event */
        $event = $this->eventRepository->get($eventQueueItem->getEventId());
        $eventQueueItem = $this->eventQueueEmailProcessor->cancelNextEmail($eventQueueItem);
        /** @var EventQueueEmailInterface $lastEmail */
        $lastEmail = $this->eventQueueEmailProcessor->getLastScheduledEmail($eventQueueItem);

        if ($this->eventQueueEmailProcessor->isEventQueueItemShouldBeCancelled($event, $lastEmail)
            || $this->hasNoMoreEmailsToSchedule($eventQueueItem)
        ) {
            $eventQueueItem->setStatus(EventQueueInterface::STATUS_CANCELLED);
        }

        $this->safeSaveEventQueueItem($eventQueueItem);

        return $eventQueueItem;
    }

    /**
     * Check if event queue item has no more emails to schedule
     *
     * @param EventQueueInterface $eventQueueItem
     * @return bool
     */
    private function hasNoMoreEmailsToSchedule($eventQueueItem)
    {
        $nextEmail = $this->eventQueueEmailProcessor->getNextEmail($eventQueueItem);
        if ($nextEmail) {
            return false;
        }

        return true;
    }

    /**
     * Send next email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueInterface
     */
    public function sendNextScheduledEmail(EventQueueInterface $eventQueueItem)
    {
        /** @var EventQueueEmailInterface $eventQueeuEmail */
        $eventQueueEmail = $this->eventQueueEmailProcessor->getLastScheduledEmail($eventQueueItem);
        if ($eventQueueEmail && $eventQueueEmail->getStatus() == EventQueueEmailInterface::STATUS_PENDING) {
            $this->eventQueueEmailScheduler->sendScheduledEmail($eventQueueEmail);
        } else {
            /** @var EmailInterface $email */
            $email = $this->eventQueueEmailProcessor->getNextEmail($eventQueueItem);
            if ($email) {
                $eventQueueItem = $this->eventQueueEmailScheduler->scheduleNextEmail($eventQueueItem, $email);

                /** @var EventQueueEmailInterface $eventQueueEmail */
                $eventQueueEmail = $this->eventQueueEmailProcessor->getLastScheduledEmail($eventQueueItem);
                $this->eventQueueEmailScheduler->sendScheduledEmail($eventQueueEmail);
            }
        }

        if ($this->hasNoMoreEmailsToSchedule($eventQueueItem)) {
            $eventQueueItem->setStatus(EventQueueInterface::STATUS_FINISHED);
        }
        $this->safeSaveEventQueueItem($eventQueueItem);

        return $eventQueueItem;
    }

    /**
     * Get next email preview
     *
     * @param EventQueueInterface $eventQueueItem
     * @return PreviewInterface|false
     */
    public function getScheduledEmailPreview(EventQueueInterface $eventQueueItem)
    {
        $preview = false;
        /** @var EventQueueEmailInterface $eventQueeuEmail */
        $eventQueueEmail = $this->eventQueueEmailProcessor->getLastScheduledEmail($eventQueueItem);
        if ($eventQueueEmail && $eventQueueEmail->getStatus() == EventQueueEmailInterface::STATUS_PENDING) {
            /** @var PreviewInterface $preview */
            $preview = $this->emailPreviewProcessor->getCreatedEmailPreview($eventQueueEmail);
        } else {
            /** @var EmailInterface $email */
            $email = $this->eventQueueEmailProcessor->getNextEmail($eventQueueItem);
            if ($email) {
                /** @var PreviewInterface $preview */
                $preview = $this->emailPreviewProcessor->getScheduledEmailPreview($eventQueueItem, $email);
            }
        }

        return $preview;
    }
}
