<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\CartConditionConverter;
use Aheadworks\Followupemail2\Model\Event\ProductConditionConverter;
use Aheadworks\Followupemail2\Model\Event\LifetimeConditionConverter;

/**
 * Class PostDataProcessor
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event
 */
class PostDataProcessor
{
    /**
     * @var CartConditionConverter
     */
    private $cartConditionConverter;

    /**
     * @var ProductConditionConverter
     */
    private $productConditionConverter;

    /**
     * @var LifetimeConditionConverter
     */
    private $lifetimeConditionConverter;

    /**
     * @param CartConditionConverter $cartConditionConverter
     * @param ProductConditionConverter $productConditionConverter
     * @param LifetimeConditionConverter $lifetimeConditionConverter
     */
    public function __construct(
        CartConditionConverter $cartConditionConverter,
        ProductConditionConverter $productConditionConverter,
        LifetimeConditionConverter $lifetimeConditionConverter
    ) {
        $this->cartConditionConverter = $cartConditionConverter;
        $this->productConditionConverter = $productConditionConverter;
        $this->lifetimeConditionConverter = $lifetimeConditionConverter;
    }

    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        $preparedData = $this->prepareLifetimeConditions($data);
        $preparedData = $this->prepareCartProductConditions($preparedData);
        $preparedData = $this->prepareStoreIds($preparedData);
        $preparedData = $this->prepareListsWithAll($preparedData);

        return $preparedData;
    }

    /**
     * Prepare lifetime conditions
     *
     * @param array $data
     * @return array
     */
    private function prepareLifetimeConditions($data)
    {
        $lifetimeCondKeys = ['lifetime_conditions', 'lifetime_value', 'lifetime_from', 'lifetime_to'];
        $lifetimeConditions = array_intersect_key($data, array_flip($lifetimeCondKeys));
        if (count($lifetimeConditions) > 0) {
            foreach ($lifetimeCondKeys as $key) {
                unset($data[$key]);
            }
            $data[EventInterface::LIFETIME_CONDITIONS] =
                $this->lifetimeConditionConverter->getConditionsSerialized($lifetimeConditions);
        } else {
            $data[EventInterface::LIFETIME_CONDITIONS] = '';
        }

        return $data;
    }

    /**
     * Prepare cart & product conditions
     *
     * @param array $data
     * @return array
     */
    private function prepareCartProductConditions($data)
    {
        if (isset($data['rule'])) {
            $data[EventInterface::CART_CONDITIONS] =
                $this->cartConditionConverter->getConditionsPrepared($data['rule']);
            $data[EventInterface::PRODUCT_CONDITIONS] =
                $this->productConditionConverter->getConditionsPrepared($data['rule']);

            unset($data['rule']);
        }

        if (!isset($data[EventInterface::CART_CONDITIONS])) {
            $data[EventInterface::CART_CONDITIONS] = '';
        }

        if (!isset($data[EventInterface::PRODUCT_CONDITIONS])) {
            $data[EventInterface::PRODUCT_CONDITIONS] = '';
        }

        return $data;
    }

    /**
     * Prepare store ids
     *
     * @param array $data
     * @return array
     */
    private function prepareStoreIds($data)
    {
        if (isset($data['store_ids'])) {
            $storeIds = $data['store_ids'];
            foreach ($storeIds as $storeValue) {
                if ($storeValue == 0) {
                    $data['store_ids'] = [0];
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Prepare lists with 'all'
     *
     * @param array $data
     * @return array
     */
    private function prepareListsWithAll($data)
    {
        $listKeys = ['customer_groups', 'product_type_ids'];

        foreach ($listKeys as $key) {
            if (isset($data[$key])) {
                $values = $data[$key];
                foreach ($values as $value) {
                    if ($value == 'all') {
                        $data[$key] = ['all'];
                        break;
                    }
                }
            }
        }

        return $data;
    }
}
