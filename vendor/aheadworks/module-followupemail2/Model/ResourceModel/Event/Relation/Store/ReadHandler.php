<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Store;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Store
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
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_fue2_event_store'), 'store_id')
                ->where('event_id = :id');
            $storeIds = $connection->fetchCol($select, ['id' => $entityId]);
            $entity->setStoreIds($storeIds);
        }
        return $entity;
    }
}
