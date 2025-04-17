<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\AwCustomerSegment;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event as EventResourceModel;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\AwCustomerSegment
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var EventResourceModel
     */
    private $eventResourceModel;

    /**
     * @param EventResourceModel $eventResourceModel
     */
    public function __construct(
        EventResourceModel $eventResourceModel
    ) {
        $this->eventResourceModel = $eventResourceModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var EventInterface $entity */
        if ($entityId = (int)$entity->getId()) {
            $awCustomerSegmentIds = $this->getAwCustomerSegmentIds($entityId);
            $entity->setAwCustomerSegmentIds($awCustomerSegmentIds);
        }
        return $entity;
    }

    /**
     * Retrieve customer segment ids array
     *
     * @param $entityId
     * @return array
     */
    private function getAwCustomerSegmentIds($entityId)
    {
        $awCustomerSegmentIds = [];

        try {
            $connection = $this->eventResourceModel->getConnection();
            $select = $connection
                ->select()
                ->from(
                    $this->eventResourceModel->getEventAwCustomerSegmentTableName(),
                    [EventResourceModel::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME]
                )->where(EventResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME . ' = :id')
            ;
            $awCustomerSegmentIds = $connection->fetchCol($select, ['id' => $entityId]);
        } catch (\Exception $exception) {
        }

        return $awCustomerSegmentIds;
    }
}
