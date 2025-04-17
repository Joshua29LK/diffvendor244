<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue as QueueResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Queue
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Queue extends AbstractModel implements QueueInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(QueueResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($queueId)
    {
        return $this->setData(self::ID, $queueId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return $this->getData(self::EVENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventType($eventType)
    {
        return $this->setData(self::EVENT_TYPE, $eventType);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventEmailId()
    {
        return $this->getData(self::EVENT_EMAIL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventEmailId($eventEmailId)
    {
        return $this->setData(self::EVENT_EMAIL_ID, $eventEmailId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailContentId()
    {
        return $this->getData(self::EMAIL_CONTENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailContentId($emailContentId)
    {
        return $this->setData(self::EMAIL_CONTENT_ID, $emailContentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventQueueEmailId()
    {
        return $this->getData(self::EVENT_QUEUE_EMAIL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventQueueEmailId($eventQueueEmailId)
    {
        return $this->setData(self::EVENT_QUEUE_EMAIL_ID, $eventQueueEmailId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledAt()
    {
        return $this->getData(self::SCHEDULED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setScheduledAt($scheduledAt)
    {
        return $this->setData(self::SCHEDULED_AT, $scheduledAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getSentAt()
    {
        return $this->getData(self::SENT_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSentAt($sentAt)
    {
        return $this->setData(self::SENT_AT, $sentAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderName()
    {
        return $this->getData(self::SENDER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderName($name)
    {
        return $this->setData(self::SENDER_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderEmail()
    {
        return $this->getData(self::SENDER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderEmail($email)
    {
        return $this->setData(self::SENDER_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientName()
    {
        return $this->getData(self::RECIPIENT_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientName($name)
    {
        return $this->setData(self::RECIPIENT_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientEmail()
    {
        return $this->getData(self::RECIPIENT_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientEmail($email)
    {
        return $this->setData(self::RECIPIENT_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentVersion()
    {
        return $this->getData(self::CONTENT_VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentVersion($contentVersion)
    {
        return $this->setData(self::CONTENT_VERSION, $contentVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(QueueExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
