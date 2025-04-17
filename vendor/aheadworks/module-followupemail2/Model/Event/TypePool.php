<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Model\Event\TypeInterfaceFactory as EventTypeInterfaceFactory;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;

/**
 * Class TypePool
 * @package Aheadworks\Followupemail2\Model\Event
 */
class TypePool
{
    /**
     * @var EventTypeInterfaceFactory
     */
    private $eventTypeFactory;

    /**
     * @var array
     */
    private $eventTypes = [];

    /**
     * @var string
     */
    private $defaultType;

    /**
     * @var EventTypeInterface[]
     */
    private $eventTypeInstances = [];

    /**
     * @param TypeInterfaceFactory $eventTypeFactory
     * @param string $defaultType
     * @param array $eventTypes
     */
    public function __construct(
        EventTypeInterfaceFactory $eventTypeFactory,
        $defaultType = '',
        $eventTypes = []
    ) {
        $this->eventTypeFactory = $eventTypeFactory;
        $this->defaultType = $defaultType;
        $this->eventTypes = $eventTypes;
    }

    /**
     * Retrieves event type instance
     *
     * @param string $typeCode
     * @return TypeInterface
     * @throws \Exception
     */
    public function getType($typeCode)
    {
        if (!isset($this->eventTypeInstances[$typeCode])) {
            /** @var EventTypeInterface $eventTypeInstance */
            $eventTypeInstance = $this->createEventTypeInstance($typeCode);
            $this->eventTypeInstances[$eventTypeInstance->getCode()] = $eventTypeInstance;
        }
        return $this->eventTypeInstances[$typeCode];
    }

    /**
     * Retrieves all enabled event type instances
     *
     * @return TypeInterface[]
     * @throws \Exception
     */
    public function getAllEnabledTypes()
    {
        $eventTypeInstances = [];
        foreach ($this->eventTypes as $eventType) {
            $eventTypeCode = $eventType[EventTypeInterface::CODE];
            /** @var EventTypeInterface $eventTypeInstance */
            $eventTypeInstance = $this->createEventTypeInstance($eventTypeCode);
            $this->eventTypeInstances[$eventTypeCode] = $eventTypeInstance;

            if ($this->eventTypeInstances[$eventTypeCode]->isEnabled()) {
                $eventTypeInstances[$eventTypeCode] = $this->eventTypeInstances[$eventTypeCode];
            }
        }
        return $eventTypeInstances;
    }

    /**
     * Retrieves default event type code
     *
     * @return string
     */
    public function getDefaultTypeCode()
    {
        return $this->defaultType;
    }

    /**
     * Create event type instance
     *
     * @param string $eventTypeCode
     * @return EventTypeInterface
     * @throws \Exception
     */
    private function createEventTypeInstance($eventTypeCode)
    {
        $eventData = $this->getEventData($eventTypeCode);
        $preparedEventData = $this->getPreparedEventData($eventData);
        $eventTypeInstance = $this->getEventTypeInstance($preparedEventData);
        if (!$eventTypeInstance instanceof EventTypeInterface) {
            throw new \Exception(
                sprintf('Event type instance %s does not implement required interface.', $eventTypeCode)
            );
        }
        return $eventTypeInstance;
    }

    /**
     * Retrieve event data by event type code
     *
     * @param $eventTypeCode
     * @return array|null
     */
    private function getEventData($eventTypeCode)
    {
        $eventData = null;
        foreach ($this->eventTypes as $eventType) {
            if ($eventTypeCode == $eventType[EventTypeInterface::CODE]) {
                $eventData = $eventType;
                break;
            }
        }
        return $eventData;
    }

    /**
     * Prepare event data
     *
     * @param array $eventData
     * @return array
     */
    private function getPreparedEventData($eventData)
    {
        $preparedEventData = $eventData;
        if (!empty($eventData[TypeInterface::TITLE])) {
            $preparedEventData[TypeInterface::TITLE] = __($eventData[TypeInterface::TITLE]);
        }
        return $preparedEventData;
    }

    /**
     * Create event type instance
     *
     * @param array $preparedEventData
     * @return EventTypeInterface
     */
    private function getEventTypeInstance($preparedEventData)
    {
        /** @var EventTypeInterface $eventTypeInstance */
        $eventTypeInstance = $this->eventTypeFactory->create(['data' => $preparedEventData]);
        return $eventTypeInstance;
    }
}
