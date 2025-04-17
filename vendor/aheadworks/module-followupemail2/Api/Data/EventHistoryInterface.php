<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EventHistoryInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventHistoryInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const REFERENCE_ID  = 'reference_id';
    const EVENT_TYPE    = 'event_type';
    const EVENT_DATA    = 'event_data';
    const TRIGGERED_AT  = 'triggered_at';
    const PROCESSED     = 'processed';
    /**#@-*/

    /**
     * Get event history ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set event history ID
     *
     * @param int|null $eventHistoryId
     * @return $this
     */
    public function setId($eventHistoryId);

    /**
     * Get reference ID
     *
     * @return int
     */
    public function getReferenceId();

    /**
     * Set reference ID
     *
     * @param int $referenceId
     * @return $this
     */
    public function setReferenceId($referenceId);

    /**
     * Get event type
     *
     * @return string
     */
    public function getEventType();

    /**
     * Get event type
     *
     * @param string $eventType
     * @return $this
     */
    public function setEventType($eventType);

    /**
     * Get event data (serialized)
     *
     * @return string
     */
    public function getEventData();

    /**
     * Set event data (serialized)
     *
     * @param string $eventData
     * @return $this
     */
    public function setEventData($eventData);

    /**
     * Get trigger time
     *
     * @return string
     */
    public function getTriggeredAt();

    /**
     * Set trigger time
     *
     * @param string $triggeredAt
     * @return $this
     */
    public function setTriggeredAt($triggeredAt);

    /**
     * Get processed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getProcessed();

    /**
     * Set status
     *
     * @param bool $processed
     * @return $this
     */
    public function setProcessed($processed);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EventHistoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EventHistoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EventHistoryExtensionInterface $extensionAttributes
    );
}
