<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EventQueueInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventQueueInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const EVENT_ID          = 'event_id';
    const REFERENCE_ID      = 'reference_id';
    const EVENT_TYPE        = 'event_type';
    const EVENT_DATA        = 'event_data';
    const CREATED_AT        = 'created_at';
    const SECURITY_CODE     = 'security_code';
    const STATUS            = 'status';
    const EMAILS            = 'emails';
    /**#@-*/

    /**#@+
     * Status values
     */
    const STATUS_PROCESSING     = 0;
    const STATUS_CANCELLED      = 1;
    const STATUS_FINISHED       = 2;
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get event id
     *
     * @return int
     */
    public function getEventId();

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

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
     * Get created time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get security code
     *
     * @return string
     */
    public function getSecurityCode();

    /**
     * Set security code
     *
     * @param string $securityCode
     * @return $this
     */
    public function setSecurityCode($securityCode);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get emails
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface[]
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEmails();

    /**
     * Set emails
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface[] $emails
     * @return $this
     */
    public function setEmails($emails);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EventQueueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EventQueueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EventQueueExtensionInterface $extensionAttributes
    );
}
