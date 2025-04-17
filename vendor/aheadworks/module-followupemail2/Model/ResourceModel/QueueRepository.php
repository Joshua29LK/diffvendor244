<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\Collection as QueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class QueueRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var QueueInterfaceFactory
     */
    private $queueFactory;

    /**
     * @var QueueSearchResultsInterfaceFactory
     */
    private $queueSearchResultsFactory;

    /**
     * @var QueueCollectionFactory
     */
    private $queueCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param EntityManager $entityManager
     * @param QueueInterfaceFactory $queueFactory
     * @param QueueSearchResultsInterfaceFactory $queueSearchResultsFactory
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        QueueInterfaceFactory $queueFactory,
        QueueSearchResultsInterfaceFactory $queueSearchResultsFactory,
        QueueCollectionFactory $queueCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->queueFactory = $queueFactory;
        $this->queueSearchResultsFactory = $queueSearchResultsFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queueItem)
    {
        try {
            $this->entityManager->save($queueItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$queueItem->getId()]);
        return $this->get($queueItem->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($queueItemId)
    {
        if (!isset($this->instances[$queueItemId])) {
            /** @var QueueInterface $queueItem */
            $queueItem = $this->queueFactory->create();
            $this->entityManager->load($queueItem, $queueItemId);
            if (!$queueItem->getId()) {
                throw NoSuchEntityException::singleField('id', $queueItemId);
            }
            $this->instances[$queueItemId] = $queueItem;
        }
        return $this->instances[$queueItemId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var QueueSearchResultsInterface $searchResults */
        $searchResults = $this->queueSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var QueueCollection $collection */
        $collection = $this->queueCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, QueueInterface::class);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }

        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $queueItems = [];
        /** @var \Aheadworks\Followupemail2\Model\Queue $queueModel */
        foreach ($collection as $queueModel) {
            /** @var QueueInterface $queue */
            $queue = $this->queueFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $queue,
                $queueModel->getData(),
                QueueInterface::class
            );
            $queueItems[] = $queue;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($queueItems)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $queueItem)
    {
        return $this->deleteById($queueItem->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($queueItemId)
    {
        /** @var QueueInterface $queueItem */
        $queueItem = $this->queueFactory->create();
        $this->entityManager->load($queueItem, $queueItemId);
        if (!$queueItem->getId()) {
            throw NoSuchEntityException::singleField('id', $queueItemId);
        }
        $this->entityManager->delete($queueItem);
        unset($this->instances[$queueItemId]);
        return true;
    }
}
