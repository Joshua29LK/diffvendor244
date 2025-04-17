<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Statistics;

use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class DeleteHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\Statistics
 * @codeCoverageIgnore
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @param StatisticsManagementInterface $statisticsManagement
     */
    public function __construct(
        StatisticsManagementInterface $statisticsManagement
    ) {
        $this->statisticsManagement = $statisticsManagement;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $this->statisticsManagement->updateByEventId($entityId);

        return $entity;
    }
}
