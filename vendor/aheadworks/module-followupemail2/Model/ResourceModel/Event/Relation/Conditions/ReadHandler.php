<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Conditions;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Conditions
 * @codeCoverageIgnore
 */
class ReadHandler implements ExtensionInterface
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
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(EventInterface::class)->getEntityConnectionName()
            );
            $tableName = $this->resourceConnection->getTableName('aw_fue2_event_conditions');

            $select = $connection->select()
                ->from($tableName)
                ->where('event_id = :id');
            $conditions = $connection->fetchAll($select, ['id' => $entityId]);

            $entity = $this->setConditions($entity, $conditions);
        }
        return $entity;
    }

    /**
     * @param EventInterface $entity
     * @param array $conditions
     * @return EventInterface
     */
    private function setConditions($entity, $conditions)
    {
        $entity->setCartConditions('');
        $entity->setProductConditions('');
        $entity->setLifetimeConditions('');

        foreach ($conditions as $condition) {
            switch ($condition['type']) {
                case EventInterface::TYPE_CONDITIONS_CART:
                    $entity->setCartConditions($condition['value']);
                    break;
                case EventInterface::TYPE_CONDITIONS_PRODUCT:
                    $entity->setProductConditions($condition['value']);
                    break;
                case EventInterface::TYPE_CONDITIONS_LIFETIME:
                    $entity->setLifetimeConditions($condition['value']);
                    break;
            }
        }

        return $entity;
    }
}
