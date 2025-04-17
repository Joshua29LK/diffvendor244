<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Email
 * @package Aheadworks\Followupemail2\Model\Email
 * @codeCoverageIgnore
 */
class Email extends AbstractExtensibleObject implements EventQueueEmailInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_get(self::ID);
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
    public function getEventQueueId()
    {
        return $this->_get(self::EVENT_QUEUE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventQueueId($eventQueueId)
    {
        return $this->setData(self::EVENT_QUEUE_ID, $eventQueueId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
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
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
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
    public function getExtensionAttributes()
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(EventQueueEmailExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
