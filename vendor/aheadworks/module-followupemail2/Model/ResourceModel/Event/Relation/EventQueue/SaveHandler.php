<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\EventQueue;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Relation\EventQueue
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        EventQueueManagementInterface $eventQueueManagement,
        CampaignRepositoryInterface $campaignRepository
    ) {
        $this->eventQueueManagement = $eventQueueManagement;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var EventInterface $entity */
        $entityId = (int)$entity->getId();
        $cancelEmailChainsNeeded = false;
        if ($entity->getStatus() == EventInterface::STATUS_DISABLED) {
            $cancelEmailChainsNeeded = true;
        } else {
            try {
                /** @var CampaignInterface $campaign */
                $campaign = $this->campaignRepository->get($entity->getCampaignId());
                if ($campaign->getStatus() == CampaignInterface::STATUS_DISABLED) {
                    $cancelEmailChainsNeeded = true;
                }
            } catch (NoSuchEntityException $e) {
                // do nothing
            }
        }

        if ($cancelEmailChainsNeeded) {
            $this->eventQueueManagement->cancelEventsByEventId($entityId);
        }
        return $entity;
    }
}
