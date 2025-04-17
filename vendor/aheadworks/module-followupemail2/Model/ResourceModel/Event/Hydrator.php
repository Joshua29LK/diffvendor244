<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\Hydrator as EntityManagerHydrator;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\MapperPool;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class Hydrator
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event
 * @codeCoverageIgnore
 */
class Hydrator extends EntityManagerHydrator
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param TypeResolver $typeResolver
     * @param MapperPool $mapperPool
     * @param Serializer $serializer
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        TypeResolver $typeResolver,
        MapperPool $mapperPool,
        Serializer $serializer
    ) {
        parent::__construct($dataObjectProcessor, $dataObjectHelper, $typeResolver, $mapperPool);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($entity)
    {
        $entity = $this->prepareCartConditionsBeforeExtract($entity);
        $entity = $this->prepareProductConditionsBeforeExtract($entity);

        $data = parent::extract($entity);
        if (isset($data[EventInterface::PRODUCT_TYPE_IDS]) && is_array($data[EventInterface::PRODUCT_TYPE_IDS])) {
            $data[EventInterface::PRODUCT_TYPE_IDS] = implode(',', $data[EventInterface::PRODUCT_TYPE_IDS]);
        }
        if (isset($data[EventInterface::CUSTOMER_GROUPS]) && is_array($data[EventInterface::CUSTOMER_GROUPS])) {
            $data[EventInterface::CUSTOMER_GROUPS] = implode(',', $data[EventInterface::CUSTOMER_GROUPS]);
        }
        if (isset($data[EventInterface::ORDER_STATUSES]) && is_array($data[EventInterface::ORDER_STATUSES])) {
            $data[EventInterface::ORDER_STATUSES] = implode(',', $data[EventInterface::ORDER_STATUSES]);
        }

        return $data;
    }

    /**
     * Prepare entity cart conditions before parent extract method is called
     *
     * @param object $entity
     * @return object
     */
    private function prepareCartConditionsBeforeExtract($entity)
    {
        $origCartCond = $entity->getCartConditions();
        if (isset($origCartCond) && is_array($origCartCond)) {
            $conditions = $this->serializer->serialize($entity->getCartConditions());
            $entity->setCartConditions($conditions);
        }
        return $entity;
    }

    /**
     * Prepare entity product conditions before parent extract method is called
     *
     * @param object $entity
     * @return object
     */
    private function prepareProductConditionsBeforeExtract($entity)
    {
        $origProductCond = $entity->getProductConditions();
        if (isset($origProductCond) && is_array($origProductCond)) {
            $conditions = $this->serializer->serialize($entity->getProductConditions());
            $entity->setProductConditions($conditions);
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($entity, array $data)
    {
        /** @var EventInterface $entity */
        $entity = parent::hydrate($entity, $data);
        if (!is_array($entity->getProductTypeIds())) {
            $entity->setProductTypeIds(explode(',', $entity->getProductTypeIds() ?? ''));
        }
        if (!is_array($entity->getCustomerGroups())) {
            $entity->setCustomerGroups(explode(',', $entity->getCustomerGroups() ?? ''));
        }
        if (!is_array($entity->getOrderStatuses())) {
            $entity->setOrderStatuses(explode(',', $entity->getOrderStatuses() ?? ''));
        }

        return $entity;
    }
}
