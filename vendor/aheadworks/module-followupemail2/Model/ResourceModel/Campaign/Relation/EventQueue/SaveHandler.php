<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Relation\EventQueue;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Relation\EventQueue
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @param EventQueueManagementInterface $eventQueueManagement
     */
    public function __construct(
        EventQueueManagementInterface $eventQueueManagement
    ) {
        $this->eventQueueManagement = $eventQueueManagement;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var CampaignInterface $entity */
        $entityId = (int)$entity->getId();
        if ($entity->getStatus() == CampaignInterface::STATUS_DISABLED) {
            $this->eventQueueManagement->cancelEventsByCampaignId($entityId);
        }

        return $entity;
    }
}
