<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\CartConditionFactory;

/**
 * Class CartConditionConverter
 * @package Aheadworks\Followupemail2\Model\Event
 */
class CartConditionConverter
{
    /**
     * @var CartConditionFactory
     */
    private $cartConditionFactory;

    /**
     * @param CartConditionFactory $cartConditionFactory
     */
    public function __construct(
        CartConditionFactory $cartConditionFactory
    ) {
        $this->cartConditionFactory= $cartConditionFactory;
    }

    /**
     * Get cart condition
     *
     * @param EventInterface $event
     * @return CartCondition
     */
    public function getCondition(EventInterface $event)
    {
        /** @var CartCondition $cartCondition */
        $cartCondition = $this->cartConditionFactory->create();
        $cartCondition->setData('conditions_serialized', $event->getCartConditions());
        return $cartCondition;
    }

    /**
     * Get serialized cart conditions from submitted form data
     *
     * @param array $data
     * @return string
     */
    public function getConditionsPrepared($data)
    {
        $cartCondPrepared = '';
        $conditionData = $this->explodeConditionData($data);

        if (isset($conditionData['cartCondition'])) {
            /** @var CartCondition $cartCondition */
            $cartCondition = $this->cartConditionFactory->create();
            $cartCondition->loadPost($conditionData['cartCondition']);
            if ($cartCondition->getConditions()) {
                $cartCondPrepared = $cartCondition->getConditions()->asArray();
            }
        }

        return $cartCondPrepared;
    }

    /**
     * Explode condition data from submitted rule data
     *
     * @param array $data
     * @return array
     */
    private function explodeConditionData($data)
    {
        $result = [];

        foreach ($data['conditions'] as $key => $value) {
            if (substr($key, 0, 1) == CartCondition::CONDITION_ID) {
                $result['cartCondition']['conditions'][$key] = $value;
            }
        }
        return $result;
    }
}
