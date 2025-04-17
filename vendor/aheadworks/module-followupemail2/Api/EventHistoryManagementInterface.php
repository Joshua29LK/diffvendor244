<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;

/**
 * Interface EventHistoryManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface EventHistoryManagementInterface
{
    /**
     * Add event data to event history
     *
     * @param string $eventCode
     * @param array $eventData
     * @return bool
     */
    public function addEvent($eventCode, $eventData);

    /**
     * Process unprocessed event history items
     *
     * @param int $maxItemsCount
     * @return bool
     */
    public function processUnprocessedItems($maxItemsCount);
}
