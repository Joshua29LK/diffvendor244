<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\Validator as EventQueueValidator;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler as EventQueueEmailScheduler;

/**
 * Class EmailProcessor
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class EmailProcessor
{
    /**
     * @var EventQueueEmailInterfaceFactory
     */
    private $eventQueueEmailFactory;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var EventQueueValidator
     */
    private $eventQueueValidator;

    /**
     * @var EventQueueEmailScheduler
     */
    private $eventQueueEmailScheduler;

    /**
     * @param EventQueueEmailInterfaceFactory $eventQueueEmailFactory
     * @param EmailManagementInterface $emailManagement
     * @param Validator $eventQueueValidator
     * @param EmailScheduler $eventQueueEmailScheduler
     */
    public function __construct(
        EventQueueEmailInterfaceFactory $eventQueueEmailFactory,
        EmailManagementInterface $emailManagement,
        EventQueueValidator $eventQueueValidator,
        EventQueueEmailScheduler $eventQueueEmailScheduler
    ) {
        $this->eventQueueEmailFactory = $eventQueueEmailFactory;
        $this->emailManagement = $emailManagement;
        $this->eventQueueValidator = $eventQueueValidator;
        $this->eventQueueEmailScheduler = $eventQueueEmailScheduler;
    }

    /**
     * Process
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueInterface
     */
    public function process($eventQueueItem)
    {
        $lastSentDate = $this->getLastEmailSentDate($eventQueueItem);

        /** @var EmailInterface $nextEmail */
        $nextEmail = $this->emailManagement->getNextEmailToSend(
            $eventQueueItem->getEventId(),
            count($eventQueueItem->getEmails())
        );
        if ($nextEmail) {
            if ($this->eventQueueValidator->isEmailValidToSend($nextEmail, $lastSentDate)) {
                $eventQueueItem = $this->eventQueueEmailScheduler->scheduleNextEmail($eventQueueItem, $nextEmail);
                if (!$this->getNextEmail($eventQueueItem)) {
                    $eventQueueItem->setStatus(EventQueueInterface::STATUS_FINISHED);
                }
            }
        } else {
            $eventQueueItem->setStatus(EventQueueInterface::STATUS_FINISHED);
        }

        return $eventQueueItem;
    }

    /**
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueInterface
     */
    public function cancelNextEmail($eventQueueItem)
    {
        /** @var EventQueueEmailInterface[] $emails */
        $emails = $eventQueueItem->getEmails();
        if ($this->isLastScheduledEmailPending($emails)) {
            /** @var EventQueueEmailInterface $email */
            $email = end($emails);
            $emailIndex = key($emails);
            $this->eventQueueEmailScheduler->cancelScheduledEmail($email);
            $email->setStatus(EventQueueEmailInterface::STATUS_CANCELLED);
            $emails[$emailIndex] = $email;
        } else {
            /** @var EventQueueEmailInterface $email */
            $email = $this->eventQueueEmailFactory->create();
            $email
                ->setEventQueueId($eventQueueItem->getId())
                ->setStatus(EventQueueEmailInterface::STATUS_CANCELLED);
            array_push($emails, $email);
        }
        $eventQueueItem->setEmails($emails);
        return $eventQueueItem;
    }

    /**
     * Get last email sent date
     *
     * @param EventQueueInterface $eventQueueItem
     * @return string
     */
    public function getLastEmailSentDate($eventQueueItem)
    {
        $lastScheduledEmail = $this->getLastScheduledEmail($eventQueueItem);
        if ($lastScheduledEmail) {
            $lastSentDate = $lastScheduledEmail->getUpdatedAt();
        } else {
            $lastSentDate = $eventQueueItem->getCreatedAt();
        }
        return $lastSentDate;
    }

    /**
     * Get last scheduled email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EventQueueEmailInterface|false
     */
    public function getLastScheduledEmail($eventQueueItem)
    {
        $queueEmail = false;
        /** @var EventQueueEmailInterface[] $queueEmails */
        $queueEmails = $eventQueueItem->getEmails();
        $processedEmailsCount = count($queueEmails);
        if ($processedEmailsCount > 0) {
            /** @var EventQueueEmailInterface $queueEmail */
            $queueEmail = end($queueEmails);
        }

        return $queueEmail;
    }

    /**
     * Check if a queue email is pending
     *
     * @param EventQueueEmailInterface $queueEmail
     * @return bool
     */
    public function isPending($queueEmail)
    {
        return ($queueEmail->getStatus() == EventQueueEmailInterface::STATUS_PENDING);
    }

    /**
     * Check if a queue email is pending
     *
     * @param EventInterface $event
     * @param EventQueueEmailInterface $queueEmail
     * @return bool
     */
    public function isEventQueueItemShouldBeCancelled($event, $queueEmail)
    {
        return ($event->getFailedEmailsMode() == EventInterface::FAILED_EMAILS_CANCEL
            && ($queueEmail->getStatus() == EventQueueEmailInterface::STATUS_FAILED
                || $queueEmail->getStatus() == EventQueueEmailInterface::STATUS_CANCELLED));
    }

    /**
     * Get next not sent email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EmailInterface|false
     */
    public function getNextNotSentEmail($eventQueueItem)
    {
        $eventQueueEmails = $eventQueueItem->getEmails();
        $lastScheduledEmailIndex = count($eventQueueEmails);
        if ($lastScheduledEmailIndex > 0 && $this->isLastScheduledEmailPending($eventQueueEmails)) {
            $lastScheduledEmailIndex--;
        }
        return $this->emailManagement->getNextEmailToSend($eventQueueItem->getEventId(), $lastScheduledEmailIndex);
    }

    /**
     * Get next email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return EmailInterface|false
     */
    public function getNextEmail($eventQueueItem)
    {
        $eventQueueEmails = $eventQueueItem->getEmails();
        $nextEmailIndex = count($eventQueueEmails);
        return $this->emailManagement->getNextEmailToSend($eventQueueItem->getEventId(), $nextEmailIndex);
    }

    /**
     * Check if last scheduled email is in pending status
     *
     * @param EventQueueEmailInterface[] $eventQueueEmails
     * @return bool
     */
    public function isLastScheduledEmailPending($eventQueueEmails)
    {
        if (count($eventQueueEmails) > 0) {
            /** @var EventQueueEmailInterface $lastScheduledEmail */
            $lastScheduledEmail = end($eventQueueEmails);
            if ($lastScheduledEmail->getStatus() == EventQueueEmailInterface::STATUS_PENDING) {
                return true;
            }
        }
        return false;
    }
}
