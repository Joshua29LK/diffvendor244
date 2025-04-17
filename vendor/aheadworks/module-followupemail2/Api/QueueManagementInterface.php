<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;

/**
 * Interface QueueManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface QueueManagementInterface
{
    /**
     * Send email
     *
     * @param QueueInterface $queue
     * @return bool
     * @throws LocalizedException
     */
    public function send(QueueInterface $queue);

    /**
     * Send email by id
     *
     * @param int $queueId
     * @return bool
     * @throws LocalizedException
     */
    public function sendById($queueId);

    /**
     * Get email preview
     *
     * @param QueueInterface $queue
     * @return PreviewInterface
     */
    public function getPreview(QueueInterface $queue);

    /**
     * Send test email
     *
     * @param EmailInterface $email
     * @param int $contentId
     * @return bool
     * @throws LocalizedException
     */
    public function sendTest(EmailInterface $email, $contentId);

    /**
     * Schedule email
     *
     * @param EventQueueInterface $eventQueueItem
     * @param EmailInterface $email
     * @param int $eventQueueEmailId
     * @return bool
     */
    public function schedule(EventQueueInterface $eventQueueItem, EmailInterface $email, $eventQueueEmailId);

    /**
     * Cancel email by event queue email id
     *
     * @param int $eventQueueEmailId
     * @return bool
     */
    public function cancelByEventQueueEmailId($eventQueueEmailId);

    /**
     * Send email by event queue email id
     *
     * @param int $eventQueueEmailId
     * @return bool
     */
    public function sendByEventQueueEmailId($eventQueueEmailId);

    /**
     * Send scheduled emails
     *
     * @param int $emailsCount
     * @return bool
     */
    public function sendScheduledEmails($emailsCount);

    /**
     * Clear queue
     *
     * @param int $keepForDays
     * @return bool
     */
    public function clearQueue($keepForDays);
}
