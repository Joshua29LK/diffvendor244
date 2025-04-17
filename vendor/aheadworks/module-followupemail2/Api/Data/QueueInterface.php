<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface QueueInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface QueueInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const EVENT_ID              = 'event_id';
    const EVENT_TYPE            = 'event_type';
    const EVENT_EMAIL_ID        = 'event_email_id';
    const EMAIL_CONTENT_ID      = 'email_content_id';
    const EVENT_QUEUE_EMAIL_ID  = 'event_queue_email_id';
    const STATUS                = 'status';
    const CREATED_AT            = 'created_at';
    const SCHEDULED_AT          = 'scheduled_at';
    const SENT_AT               = 'sent_at';
    const STORE_ID              = 'store_id';
    const SENDER_NAME           = 'sender_name';
    const SENDER_EMAIL          = 'sender_email';
    const RECIPIENT_NAME        = 'recipient_name';
    const RECIPIENT_EMAIL       = 'recipient_email';
    const SUBJECT               = 'subject';
    const CONTENT               = 'content';
    const CONTENT_VERSION       = 'content_version';
    /**#@-*/

    /**#@+
     * Status values
     */
    const STATUS_PENDING    = 1;
    const STATUS_SENT       = 2;
    const STATUS_FAILED     = 3;
    const STATUS_CANCELLED  = 4;
    /**#@-*/

    /**
     * Get queue ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set queue ID
     *
     * @param int $queueId
     * @return $this
     */
    public function setId($queueId);

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
     * Get event type
     *
     * @return string
     */
    public function getEventType();

    /**
     * Set event type
     *
     * @param string $eventType
     * @return $this
     */
    public function setEventType($eventType);

    /**
     * Get event email id
     *
     * @return int
     */
    public function getEventEmailId();

    /**
     * Set event email id
     *
     * @param int $eventEmailId
     * @return $this
     */
    public function setEventEmailId($eventEmailId);

    /**
     * Get email content id
     *
     * @return int
     */
    public function getEmailContentId();

    /**
     * Set email content id
     *
     * @param int $emailContentId
     * @return $this
     */
    public function setEmailContentId($emailContentId);

    /**
     * Get event queue email id
     *
     * @return int
     */
    public function getEventQueueEmailId();

    /**
     * Set event queue email id
     *
     * @param int $eventQueueEmailId
     * @return $this
     */
    public function setEventQueueEmailId($eventQueueEmailId);

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
     * Get creation time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get scheduled time
     *
     * @return string
     */
    public function getScheduledAt();

    /**
     * Set scheduled time
     *
     * @param string $scheduledAt
     * @return $this
     */
    public function setScheduledAt($scheduledAt);

    /**
     * Get sent time
     *
     * @return string|null
     */
    public function getSentAt();

    /**
     * Set sent time
     *
     * @param string $sentAt
     * @return $this
     */
    public function setSentAt($sentAt);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName();

    /**
     * Set sender name
     *
     * @param string $name
     * @return $this
     */
    public function setSenderName($name);

    /**
     * Get sender email
     *
     * @return string
     */
    public function getSenderEmail();

    /**
     * Set sender email
     *
     * @param string $email
     * @return $this
     */
    public function setSenderEmail($email);

    /**
     * Get recipient name
     *
     * @return string
     */
    public function getRecipientName();

    /**
     * Set recipient name
     *
     * @param string $name
     * @return $this
     */
    public function setRecipientName($name);

    /**
     * Get recipient email
     *
     * @return string
     */
    public function getRecipientEmail();

    /**
     * Set recipient email
     *
     * @param string $email
     * @return $this
     */
    public function setRecipientEmail($email);

    /**
     * Get subject
     *
     * @return string|null
     */
    public function getSubject();

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get content version
     *
     * @return int|null
     */
    public function getContentVersion();

    /**
     * Set content version
     *
     * @param int|null $contentVersion
     * @return $this
     */
    public function setContentVersion($contentVersion);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\QueueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\QueueExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\QueueExtensionInterface $extensionAttributes
    );
}
