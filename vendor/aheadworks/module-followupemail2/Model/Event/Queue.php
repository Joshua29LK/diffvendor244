<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue as EventQueueResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class EventHistory
 * @package Aheadworks\Followupemail2\Model\Event
 * @codeCoverageIgnore
 */
class Queue extends AbstractModel implements EventQueueInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EventQueueResource::class);
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
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
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
    public function getReferenceId()
    {
        return $this->getData(self::REFERENCE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceId($referenceId)
    {
        return $this->setData(self::REFERENCE_ID, $referenceId);
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
    public function getEventData()
    {
        return $this->getData(self::EVENT_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventData($eventData)
    {
        return $this->setData(self::EVENT_DATA, $eventData);
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
    public function getSecurityCode()
    {
        return $this->getData(self::SECURITY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSecurityCode($securityCode)
    {
        return $this->setData(self::SECURITY_CODE, $securityCode);
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
    public function getEmails()
    {
        return $this->getData(self::EMAILS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmails($emails)
    {
        return $this->setData(self::EMAILS, $emails);
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
    public function setExtensionAttributes(EventQueueExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
