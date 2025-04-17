<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Relation\Email;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class ReadHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Relation\Email
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
     * @var EventQueueEmailInterfaceFactory
     */
    private $eventQueueEmailFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param EventQueueEmailInterfaceFactory $eventQueueEmailFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        EventQueueEmailInterfaceFactory $eventQueueEmailFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->eventQueueEmailFactory = $eventQueueEmailFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(EventQueueInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_fue2_event_queue_email'), ['*'])
                ->where('event_queue_id = :id')
                ->order('id ASC');
            $allEmailsData = $connection->fetchAll($select, ['id' => $entityId]);

            $emailDataObjects = [];
            foreach ($allEmailsData as $emailData) {
                /** @var EventQueueEmailInterface $eventQueueEmail */
                $eventQueueEmail = $this->eventQueueEmailFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $eventQueueEmail,
                    $emailData,
                    EventQueueEmailInterface::class
                );
                $emailDataObjects[] = $eventQueueEmail;
            }
            $entity->setEmails($emailDataObjects);
        }
        return $entity;
    }
}
