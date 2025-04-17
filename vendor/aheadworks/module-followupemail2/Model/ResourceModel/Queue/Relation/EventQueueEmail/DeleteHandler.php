<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Queue\Relation\EventQueueEmail;

use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Collection as EventQueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\CollectionFactory as EventQueueCollectionFactory;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class DeleteHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Queue\Relation\EventQueueEmail
 * @codeCoverageIgnore
 */
class DeleteHandler implements ExtensionInterface
{
    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var EventQueueCollectionFactory
     */
    private $eventQueueCollectionFactory;

    /**
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param EventQueueCollectionFactory $eventQueueCollectionFactory
     */
    public function __construct(
        EventQueueRepositoryInterface $eventQueueRepository,
        EventQueueCollectionFactory $eventQueueCollectionFactory
    ) {
        $this->eventQueueRepository = $eventQueueRepository;
        $this->eventQueueCollectionFactory = $eventQueueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var QueueInterface $entity */
        $eventQueueEmailId = $entity->getEventQueueEmailId();
        $status = $entity->getStatus();
        if ($eventQueueEmailId && $status == QueueInterface::STATUS_PENDING) {
            /** @var EventQueueCollection $eventQueueCollection */
            $eventQueueCollection = $this->eventQueueCollectionFactory->create();
            $eventQueueCollection->filterByEventQueueEmailId($eventQueueEmailId);
            foreach ($eventQueueCollection as $eventQueueModel) {
                /** @var EventQueueInterface $eventQueueItem */
                $eventQueueItem = $this->eventQueueRepository->get($eventQueueModel->getId());
                $emails = $eventQueueItem->getEmails();

                foreach ($emails as &$email) {
                    if ($email->getId() == $eventQueueEmailId) {
                        $email->setStatus(EventQueueEmailInterface::STATUS_CANCELLED);
                    }
                }

                $eventQueueItem->setEmails($emails);
                $this->eventQueueRepository->save($eventQueueItem);
            }
        }

        return $entity;
    }
}
