<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory as EventHistoryResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class EventHistory
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class EventHistory extends AbstractModel implements EventHistoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EventHistoryResource::class);
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
    public function setId($eventHistoryId)
    {
        return $this->setData(self::ID, $eventHistoryId);
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
    public function getTriggeredAt()
    {
        return $this->getData(self::TRIGGERED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTriggeredAt($triggeredAt)
    {
        return $this->setData(self::TRIGGERED_AT, $triggeredAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessed()
    {
        return $this->getData(self::PROCESSED);
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessed($processed)
    {
        return $this->setData(self::PROCESSED, $processed);
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
    public function setExtensionAttributes(EventHistoryExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
