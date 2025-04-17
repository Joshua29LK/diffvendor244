<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class CampaignRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @var CampaignInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CampaignInterfaceFactory
     */
    private $campaignFactory;

    /**
     * @var CampaignSearchResultsInterfaceFactory
     */
    private $campaignSearchResultsFactory;

    /**
     * @var CampaignCollectionFactory
     */
    private $campaignCollectionFactory;

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
     * @param CampaignInterfaceFactory $campaignFactory
     * @param CampaignSearchResultsInterfaceFactory $campaignSearchResultsFactory
     * @param CampaignCollectionFactory $campaignCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        CampaignInterfaceFactory $campaignFactory,
        CampaignSearchResultsInterfaceFactory $campaignSearchResultsFactory,
        CampaignCollectionFactory $campaignCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->campaignFactory = $campaignFactory;
        $this->campaignSearchResultsFactory = $campaignSearchResultsFactory;
        $this->campaignCollectionFactory = $campaignCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CampaignInterface $campaign)
    {
        try {
            $this->entityManager->save($campaign);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$campaign->getId()]);
        return $this->get($campaign->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($campaignId)
    {
        if (!isset($this->instances[$campaignId])) {
            /** @var CampaignInterface $campaign */
            $campaign = $this->campaignFactory->create();
            $this->entityManager->load($campaign, $campaignId);
            if (!$campaign->getId()) {
                throw NoSuchEntityException::singleField('id', $campaignId);
            }
            $this->instances[$campaignId] = $campaign;
        }
        return $this->instances[$campaignId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CampaignSearchResultsInterface $searchResults */
        $searchResults = $this->campaignSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var CampaignCollection $collection */
        $collection = $this->campaignCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, CampaignInterface::class);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == CampaignInterface::START_DATE) {
                    $collection->addStartDateFilter($filter->getValue());
                } else if ($filter->getField() == CampaignInterface::END_DATE) {
                    $collection->addEndDateFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[] = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
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

        $campaigns = [];
        /** @var \Aheadworks\Followupemail2\Model\Campaign $campaignModel */
        foreach ($collection as $campaignModel) {
            /** @var CampaignInterface $campaign */
            $campaign = $this->campaignFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $campaign,
                $campaignModel->getData(),
                CampaignInterface::class
            );
            $campaigns[] = $campaign;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($campaigns)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CampaignInterface $campaign)
    {
        return $this->deleteById($campaign->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($campaignId)
    {
        /** @var CampaignInterface $campaign */
        $campaign = $this->campaignFactory->create();
        $this->entityManager->load($campaign, $campaignId);
        if (!$campaign->getId()) {
            throw NoSuchEntityException::singleField('id', $campaignId);
        }
        $this->entityManager->delete($campaign);
        unset($this->instances[$campaignId]);
        return true;
    }
}
