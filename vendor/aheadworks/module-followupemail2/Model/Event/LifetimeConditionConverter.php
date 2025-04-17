<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\LifetimeConditionFactory;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class LifetimeConditionConverter
 * @package Aheadworks\Followupemail2\Model\Event
 */
class LifetimeConditionConverter
{
    /**
     * @var LifetimeConditionFactory
     */
    private $lifetimeConditionFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param LifetimeConditionFactory $lifetimeConditionFactory
     * @param Serializer $serializer
     */
    public function __construct(
        LifetimeConditionFactory $lifetimeConditionFactory,
        Serializer $serializer
    ) {
        $this->lifetimeConditionFactory = $lifetimeConditionFactory;
        $this->serializer = $serializer;
    }

    /**
     * Get lifetime condition
     *
     * @param EventInterface $event
     * @return LifetimeCondition
     */
    public function getCondition(EventInterface $event)
    {
        /** @var LifetimeCondition $lifetimeCondition */
        $lifetimeCondition = $this->lifetimeConditionFactory->create();
        $lifetimeCondition->setConditionsSerialized($event->getLifetimeConditions());
        return $lifetimeCondition;
    }

    /**
     * Get serialized lifetime conditions from submitted form data
     *
     * @param array $data
     * @return string
     */
    public function getConditionsSerialized($data)
    {
        $conditionData = $this->explodeConditionData($data);

        /** @var LifetimeCondition $lifetimeCondition */
        $lifetimeCondition = $this->lifetimeConditionFactory->create();
        try {
            $lifetimeCondSerialized = $this->serializer->serialize($conditionData);
            $lifetimeCondition->setConditionsSerialized($lifetimeCondSerialized);
        } catch (\Exception $e) {
        }
        $lifetimeCondSerialized = $lifetimeCondition->getConditionsSerialized();

        return $lifetimeCondSerialized;
    }

    /**
     * Explode form data to condition data
     *
     * @param array $data
     * @return array
     */
    private function explodeConditionData($data)
    {
        $condData = [];
        foreach ($data as $key => $value) {
            if ($key == 'lifetime_conditions') {
                $condData['operator'] = $value;
            }
            if ($key == 'lifetime_value' ||
                $key == 'lifetime_from' ||
                $key == 'lifetime_to'
            ) {
                $index = explode('_', $key);
                $condData['params'][end($index)] = $value;
            }
        }
        return $condData;
    }
}
