<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Relation\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Relation\Email
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
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $emailObjects = $entity->getEmails();
        if ($emailObjects) {
            /** @var EventQueueEmailInterface $emailObject */
            foreach ($emailObjects as $emailObject) {
                $emailData = $this->getPreparedEmailData($emailObject, $entityId);

                $connection = $this->getConnection();
                $tableName = $this->resourceConnection->getTableName('aw_fue2_event_queue_email');
                if ($this->isEmailNew($emailData)) {
                    $connection->insert($tableName, $emailData);
                } else {
                    $connection->update($tableName, $emailData, ['id = ?' => (int)$emailObject->getId()]);
                }
            }
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
            $this->metadataPool->getMetadata(EventQueueInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Retrieve email data, prepared for saving
     *
     * @param EventQueueEmailInterface $emailObject
     * @param int $entityId
     * @return array
     */
    private function getPreparedEmailData($emailObject, $entityId)
    {
        $emailObject->setEventQueueId($entityId);

        $emailData = $this->dataObjectProcessor->buildOutputDataArray(
            $emailObject,
            EventQueueEmailInterface::class
        );

        unset($emailData[EventQueueEmailInterface::UPDATED_AT]);
        if ($this->isEmailNew($emailData)) {
            unset($emailData[EventQueueEmailInterface::CREATED_AT]);
        }

        return $emailData;
    }

    /**
     * Check if email is new
     *
     * @param array $emailData
     * @return bool
     */
    private function isEmailNew($emailData)
    {
        $emailId = $emailData[EventQueueEmailInterface::ID] ?? null;
        return empty($emailId);
    }
}
