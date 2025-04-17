<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EventQueueEmailInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventQueueEmailInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const EVENT_QUEUE_ID    = 'event_queue_id';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';
    const STATUS            = 'status';
    /**#@-*/

    /**#@+
     * Status values
     */
    const STATUS_PENDING        = 0;
    const STATUS_SENT           = 1;
    const STATUS_FAILED         = 2;
    const STATUS_CANCELLED      = 3;
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get event queue id
     *
     * @return int
     */
    public function getEventQueueId();

    /**
     * Set event queue id
     *
     * @param int $eventQueueId
     * @return $this
     */
    public function setEventQueueId($eventQueueId);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

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
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EventQueueEmailExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EventQueueEmailExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EventQueueEmailExtensionInterface $extensionAttributes
    );
}
