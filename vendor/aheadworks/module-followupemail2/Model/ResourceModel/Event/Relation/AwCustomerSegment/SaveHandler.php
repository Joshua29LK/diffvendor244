<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\AwCustomerSegment;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event as EventResourceModel;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\AwCustomerSegment
 */
class SaveHandler implements ExtensionInterface
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
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        /** @var EventInterface $entity */
        if ($entityId = (int)$entity->getId()) {
            $this->removeAwCustomerSegmentData($entityId);
            $awCustomerSegmentData = $this->getAwCustomerSegmentDataToSave($entity);
            $this->saveAwCustomerSegmentData($awCustomerSegmentData);
        }
        return $entity;
    }

    /**
     * Remove old customer segment records
     *
     * @param $entityId
     * @return bool
     * @throws \Exception
     */
    private function removeAwCustomerSegmentData($entityId)
    {
        $this->eventResourceModel
            ->getConnection()
            ->delete(
                $this->eventResourceModel->getEventAwCustomerSegmentTableName(),
                [EventResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME . ' = ?' => $entityId]
            );
        return true;
    }

    /**
     * Retrieve customer segment data to save in the corresponding table
     *
     * @param EventInterface $entity
     * @return array
     */
    private function getAwCustomerSegmentDataToSave($entity)
    {
        $awCustomerSegmentData = [];
        $eventId = $entity->getId();
        if (is_array($entity->getAwCustomerSegmentIds())) {
            foreach ($entity->getAwCustomerSegmentIds() as $awCustomerSegmentId) {
                $awCustomerSegmentData[] = [
                    EventResourceModel::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME =>
                        $eventId,
                    EventResourceModel::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME =>
                        $awCustomerSegmentId,
                ];
            }
        }

        return $awCustomerSegmentData;
    }

    /**
     * Save customer segment data in the corresponding table
     *
     * @param array $awCustomerSegmentData
     * @return $this
     */
    private function saveAwCustomerSegmentData($awCustomerSegmentData)
    {
        if (!empty($awCustomerSegmentData)) {
            try {
                $this->eventResourceModel
                    ->getConnection()
                    ->insertMultiple(
                        $tableName = $this->eventResourceModel->getEventAwCustomerSegmentTableName(),
                        $awCustomerSegmentData
                    )
                ;
            } catch (\Exception $exception) {
            }
        }
        return $this;
    }
}
