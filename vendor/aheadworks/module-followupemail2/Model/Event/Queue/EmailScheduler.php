<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class EmailScheduler
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class EmailScheduler
{
    /**
     * @var EventQueueEmailInterfaceFactory
     */
    private $eventQueueEmailFactory;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EventQueueEmailInterfaceFactory $eventQueueEmailFactory
     * @param QueueManagementInterface $queueManagement
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param Logger $logger
     */
    public function __construct(
        EventQueueEmailInterfaceFactory $eventQueueEmailFactory,
        QueueManagementInterface $queueManagement,
        EventQueueRepositoryInterface $eventQueueRepository,
        Logger $logger
    ) {
        $this->eventQueueEmailFactory = $eventQueueEmailFactory;
        $this->queueManagement = $queueManagement;
        $this->eventQueueRepository = $eventQueueRepository;
        $this->logger = $logger;
    }

    /**
     * Schedule next email
     *
     * @param EventQueueInterface $eventQueueItem
     * @param EmailInterface $email
     * @return EventQueueInterface
     */
    public function scheduleNextEmail($eventQueueItem, $email)
    {
        /** @var EventQueueEmailInterface $queueEmail */
        $queueEmail = $this->eventQueueEmailFactory->create();
        $queueEmail->setStatus(EventQueueEmailInterface::STATUS_PENDING);
        /** @var EventQueueEmailInterface[] $queueEmails */
        $queueEmails = $eventQueueItem->getEmails();
        $queueEmails[] = $queueEmail;
        $eventQueueItem->setEmails($queueEmails);

        try {
            $eventQueueItem = $this->eventQueueRepository->save($eventQueueItem);

            /** @var EventQueueEmailInterface[] $queueEmails */
            $queueEmails = $eventQueueItem->getEmails();
            /** @var EventQueueEmailInterface $queueEmail */
            $queueEmail = end($queueEmails);

            if (!$this->queueManagement->schedule($eventQueueItem, $email, $queueEmail->getId())) {
                $queueEmail->setStatus(EventQueueEmailInterface::STATUS_FAILED);
                try {
                    $eventQueueItem = $this->eventQueueRepository->save($eventQueueItem);
                } catch (LocalizedException $exception) {
                    $this->logger->critical($exception->getMessage());
                }
            }
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $eventQueueItem;
    }

    /**
     * Cancel scheduled email
     *
     * @param EventQueueEmailInterface $email
     * @return $this
     */
    public function cancelScheduledEmail($email)
    {
        $this->queueManagement->cancelByEventQueueEmailId($email->getId());
        return $this;
    }

    /**
     * Send scheduled email
     *
     * @param EventQueueEmailInterface $email
     * @return $this
     */
    public function sendScheduledEmail($email)
    {
        $this->queueManagement->sendByEventQueueEmailId($email->getId());
        return $this;
    }
}
