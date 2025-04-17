<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\ProductConditionFactory;

/**
 * Class ProductConditionConverter
 * @package Aheadworks\Followupemail2\Model\Event
 */
class ProductConditionConverter
{
    /**
     * @var ProductConditionFactory
     */
    private $productConditionFactory;

    /**
     * @param ProductConditionFactory $productConditionFactory
     */
    public function __construct(
        ProductConditionFactory $productConditionFactory
    ) {
        $this->productConditionFactory = $productConditionFactory;
    }

    /**
     * Get product condition
     *
     * @param EventInterface $event
     * @return ProductCondition
     */
    public function getCondition(EventInterface $event)
    {
        /** @var ProductCondition $productCondition */
        $productCondition = $this->productConditionFactory->create();
        $productCondition->setData('conditions_serialized', $event->getProductConditions());
        return $productCondition;
    }

    /**
     * Get serialized product conditions from submitted form data
     *
     * @param array $data
     * @return string
     */
    public function getConditionsPrepared($data)
    {
        $productCondPrepared = '';
        $conditionData = $this->explodeConditionData($data);

        if (isset($conditionData['productCondition'])) {
            /** @var ProductCondition $productCondition */
            $productCondition = $this->productConditionFactory->create();
            $productCondition->loadPost($conditionData['productCondition']);
            if ($productCondition->getConditions()) {
                $productCondPrepared = $productCondition->getConditions()->asArray();
            }
        }

        return $productCondPrepared;
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
            if (substr($key, 0, 1) == ProductCondition::CONDITION_ID) {
                $result['productCondition']['conditions']['1' . substr($key, 1)] = $value;
            }
        }
        return $result;
    }
}
