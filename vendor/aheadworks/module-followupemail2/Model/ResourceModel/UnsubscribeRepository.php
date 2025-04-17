<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\UnsubscribeRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe\Collection as UnsubscribeCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe\CollectionFactory as UnsubscribeCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class UnsubscribeRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnsubscribeRepository implements UnsubscribeRepositoryInterface
{
    /**
     * @var UnsubscribeInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UnsubscribeInterfaceFactory
     */
    private $unsubscribeFactory;

    /**
     * @var UnsubscribeSearchResultsInterfaceFactory
     */
    private $unsubscribeSearchResultsFactory;

    /**
     * @var UnsubscribeCollectionFactory
     */
    private $unsubscribeCollectionFactory;

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
     * @param UnsubscribeInterfaceFactory $unsubscribeFactory
     * @param UnsubscribeSearchResultsInterfaceFactory $unsubscribeSearchResultsFactory
     * @param UnsubscribeCollectionFactory $unsubscribeCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        UnsubscribeInterfaceFactory $unsubscribeFactory,
        UnsubscribeSearchResultsInterfaceFactory $unsubscribeSearchResultsFactory,
        UnsubscribeCollectionFactory $unsubscribeCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->unsubscribeFactory = $unsubscribeFactory;
        $this->unsubscribeSearchResultsFactory = $unsubscribeSearchResultsFactory;
        $this->unsubscribeCollectionFactory = $unsubscribeCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(UnsubscribeInterface $unsubscribeItem)
    {
        try {
            $this->entityManager->save($unsubscribeItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$unsubscribeItem->getId()]);
        return $this->get($unsubscribeItem->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($unsubscribeItemId)
    {
        if (!isset($this->instances[$unsubscribeItemId])) {
            /** @var UnsubscribeInterface $unsubscribeItem */
            $unsubscribeItem = $this->unsubscribeFactory->create();
            $this->entityManager->load($unsubscribeItem, $unsubscribeItemId);
            if (!$unsubscribeItem->getId()) {
                throw NoSuchEntityException::singleField('id', $unsubscribeItemId);
            }
            $this->instances[$unsubscribeItemId] = $unsubscribeItem;
        }
        return $this->instances[$unsubscribeItemId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var UnsubscribeSearchResultsInterface $searchResults */
        $searchResults = $this->unsubscribeSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var UnsubscribeCollection $collection */
        $collection = $this->unsubscribeCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, UnsubscribeInterface::class);

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

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $unsubscribeItems = [];
        /** @var \Aheadworks\Followupemail2\Model\Unsubscribe $unsubscribeModel */
        foreach ($collection as $unsubscribeModel) {
            /** @var UnsubscribeInterface $unsubscribeItem */
            $unsubscribeItem = $this->unsubscribeFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $unsubscribeItem,
                $unsubscribeModel->getData(),
                UnsubscribeInterface::class
            );
            $unsubscribeItems[] = $unsubscribeItem;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($unsubscribeItems)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UnsubscribeInterface $unsubscribeItem)
    {
        return $this->deleteById($unsubscribeItem->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($unsubscribeItemId)
    {
        /** @var UnsubscribeInterface $unsubscribeItem */
        $unsubscribeItem = $this->unsubscribeFactory->create();
        $this->entityManager->load($unsubscribeItem, $unsubscribeItemId);
        if (!$unsubscribeItem->getId()) {
            throw NoSuchEntityException::singleField('id', $unsubscribeItemId);
        }
        $this->entityManager->delete($unsubscribeItem);
        unset($this->instances[$unsubscribeItemId]);
        return true;
    }
}
