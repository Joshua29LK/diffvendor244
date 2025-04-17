<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface EventManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface EventManagementInterface
{
    /**
     * Duplicate event emails
     *
     * @param int $sourceEventId
     * @param int $destinationEventId
     * @return boolean
     */
    public function duplicateEventEmails($sourceEventId, $destinationEventId);

    /**
     * Get events by campaign id
     *
     * @param $campaignId
     * @return EventInterface[]
     */
    public function getEventsByCampaignId($campaignId);

    /**
     * Unsubscribe from an event
     *
     * @param string $securityCode
     * @return bool
     */
    public function unsubscribeFromEvent($securityCode);

    /**
     * Unsubscribe from an event type
     *
     * @param string $securityCode
     * @return bool
     */
    public function unsubscribeFromEventType($securityCode);

    /**
     * Unsubscribe from all events
     *
     * @param string $securityCode
     * @return bool
     */
    public function unsubscribeFromAll($securityCode);

    /**
     * Change campaign
     *
     * @param int $eventId
     * @param int $campaignId
     * @return EventInterface|false
     * @throws LocalizedException
     */
    public function changeCampaign($eventId, $campaignId);
}
