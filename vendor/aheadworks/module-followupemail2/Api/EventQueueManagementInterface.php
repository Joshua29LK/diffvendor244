<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface EventQueueManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface EventQueueManagementInterface
{
    /**
     * Cancel queued events
     *
     * @param string $eventCode
     * @param int $referenceId
     * @return bool
     */
    public function cancelEvents($eventCode, $referenceId);

    /**
     * Cancel queued events by campaign id
     *
     * @param int $campaignId
     * @return bool
     */
    public function cancelEventsByCampaignId($campaignId);

    /**
     * Cancel queued events by event id
     *
     * @param int $eventId
     * @param int|null $referenceId
     * @return bool
     */
    public function cancelEventsByEventId($eventId, $referenceId = null);

    /**
     * Cancel next scheduled email by event queue id
     *
     * @param int $eventQueueId
     * @return bool
     * @throws LocalizedException if an error occurs
     */
    public function cancelScheduledEmail($eventQueueId);

    /**
     * Cancel event by event queue id
     *
     * @param int $eventQueueId
     * @return bool
     * @throws LocalizedException if an error occurs
     */
    public function cancelEvent($eventQueueId);

    /**
     * Send next scheduled event queue email immediately
     *
     * @param int $eventQueueId
     * @return bool
     * @throws LocalizedException if an error occurs
     */
    public function sendNextScheduledEmail($eventQueueId);

    /**
     * Get preview of next scheduled email
     *
     * @param EventQueueInterface $eventQueueItem
     * @return PreviewInterface
     * @throws LocalizedException if an error occurs
     */
    public function getScheduledEmailPreview($eventQueueItem);

    /**
     * Add new event to queue
     *
     * @param EventInterface $event
     * @param EventHistoryInterface $eventHistoryItem
     * @param bool $preventToAddDuplicateEmails
     * @return EventQueueInterface|false
     */
    public function add(
        EventInterface $event,
        EventHistoryInterface $eventHistoryItem,
        $preventToAddDuplicateEmails = true
    );

    /**
     * Process unprocessed event queue items
     *
     * @param int $maxItemsCount
     * @return bool
     */
    public function processUnprocessedItems($maxItemsCount);
}
