<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Conditions;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Conditions
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_fue2_event_conditions');

        $connection->delete(
            $tableName,
            ['event_id = ?' => $entityId]
        );

        $data = [];
        if ($entity->getCartConditions()) {
            $data[] = [
                'event_id' => (int)$entityId,
                'type' => EventInterface::TYPE_CONDITIONS_CART,
                'value' => $entity->getCartConditions(),
            ];
        }
        if ($entity->getProductConditions()) {
            $data[] = [
                'event_id' => (int)$entityId,
                'type' => EventInterface::TYPE_CONDITIONS_PRODUCT,
                'value' => $entity->getProductConditions(),
            ];
        }
        if ($entity->getLifetimeConditions()) {
            $data[] = [
                'event_id' => (int)$entityId,
                'type' => EventInterface::TYPE_CONDITIONS_LIFETIME,
                'value' => $entity->getLifetimeConditions(),
            ];
        }

        if (count($data) > 0) {
            $connection->insertMultiple($tableName, $data);
        }

        return $entity;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(EventInterface::class)->getEntityConnectionName()
        );
    }
}
