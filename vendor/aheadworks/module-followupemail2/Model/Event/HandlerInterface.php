<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Followupemail2\Model\Event
 */
interface HandlerInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getReferenceDataKey();

    /**
     * @param array $data
     * @return bool
     */
    public function validateEventData(array $data = []);

    /**
     * Process specified event history item
     *
     * @param EventHistoryInterface $eventHistoryItem
     * @return void
     */
    public function process(EventHistoryInterface $eventHistoryItem);

    /**
     * Cancel events
     *
     * @param string $eventCode
     * @param array $data
     * @return void
     */
    public function cancelEvents($eventCode, $data = []);

    /**
     * Get event object variable name
     *
     * @return string
     */
    public function getEventObjectVariableName();

    /**
     * Get event object
     *
     * @param array $eventData
     * @return mixed
     */
    public function getEventObject($eventData);
}
