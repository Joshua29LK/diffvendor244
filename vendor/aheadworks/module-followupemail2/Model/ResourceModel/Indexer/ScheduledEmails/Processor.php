<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor as EventQueueEmailProcessor;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Processor
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails
 */
class Processor
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventQueueEmailProcessor
     */
    private $eventQueueEmailProcessor;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventRepositoryInterface $eventRepository
     * @param EventQueueEmailProcessor $eventQueueEmailProcessor
     * @param EmailManagementInterface $emailManagement
     * @param DateTimeFactory $dateTimeFactory
     * @param Serializer $serializer
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        EventRepositoryInterface $eventRepository,
        EventQueueEmailProcessor $eventQueueEmailProcessor,
        EmailManagementInterface $emailManagement,
        DateTimeFactory $dateTimeFactory,
        Serializer $serializer
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->eventQueueEmailProcessor = $eventQueueEmailProcessor;
        $this->emailManagement = $emailManagement;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param EventQueueInterface $eventQueueItem
     * @return array
     */
    public function getData($eventQueueItem)
    {
        $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());

        /** @var EmailInterface|false $nextEmail */
        $nextEmail = $this->eventQueueEmailProcessor->getNextNotSentEmail($eventQueueItem);

        $scheduledEmailData = [
            'event_queue_id' => $eventQueueItem->getId(),
            'campaign_name' => $this->getCampaignName($eventQueueItem->getEventId()),
            'event_name' => $this->getEventName($eventQueueItem->getEventId()),
            'event_type' => $eventQueueItem->getEventType(),
            'email_name' => $nextEmail ? $nextEmail->getName() : '',
            'ab_testing_mode' => $nextEmail ? $nextEmail->getAbTestingMode() : '',
            'recipient_name' => $eventData['customer_name'],
            'recipient_email' => $eventData['email'],
            'store_id' => $eventData['store_id'],
            'scheduled_to' => $this->getScheduledTo($eventQueueItem, $nextEmail),
        ];

        return $scheduledEmailData;
    }

    /**
     * Get campaign name
     *
     * @param int $eventId
     * @return string
     */
    private function getCampaignName($eventId)
    {
        try {
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($eventId);
            /** @var CampaignInterface $campaign */
            $campaign = $this->campaignRepository->get($event->getCampaignId());
            $campaignName = $campaign->getName();
        } catch (NoSuchEntityException $e) {
            $campaignName = '';
        }
        return $campaignName;
    }

    /**
     * Get event name
     *
     * @param int $eventId
     * @return string
     */
    private function getEventName($eventId)
    {
        try {
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($eventId);
            $eventName = $event->getName();
        } catch (NoSuchEntityException $e) {
            $eventName = '';
        }
        return $eventName;
    }

    /**
     * @param EventQueueInterface $eventQueueItem
     * @param EmailInterface $email
     * @return null
     */
    private function getScheduledTo($eventQueueItem, $email)
    {
        $scheduledTo = null;

        if ($email) {
            try {
                if ($this->eventQueueEmailProcessor->isLastScheduledEmailPending($eventQueueItem->getEmails())) {
                    /** @var EventQueueEmailInterface[] $eventEmails */
                    $eventEmails = $eventQueueItem->getEmails();
                    /** @var EventQueueEmailInterface $lastEmail */
                    $lastEmail = end($eventEmails);
                    $scheduledTo = $lastEmail->getUpdatedAt();
                } else {
                    $lastEmailSentDate = $this->dateTimeFactory->create(
                        $this->eventQueueEmailProcessor->getLastEmailSentDate($eventQueueItem),
                        new \DateTimeZone('UTC')
                    );
                    if ($email->getWhen() == EmailInterface::WHEN_AFTER) {
                        $lastEmailSentDate->add($this->getEmailInterval($email));
                    }
                    $scheduledTo = $lastEmailSentDate->format(DateTime::DATETIME_PHP_FORMAT);
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $scheduledTo;
    }

    /**
     * Get email interval
     *
     * @param EmailInterface $email
     * @return \DateInterval
     * @throws \Exception
     */
    private function getEmailInterval($email)
    {
        $days = $email->getEmailSendDays() . 'D';
        $hours = $email->getEmailSendHours() . 'H';
        $minutes = $email->getEmailSendMinutes() . 'M';
        return new \DateInterval('P' . $days . 'T' . $hours . $minutes);
    }
}
