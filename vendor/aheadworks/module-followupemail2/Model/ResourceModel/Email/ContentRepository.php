<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EmailContentSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\EmailContentRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Content\Collection as EmailContentCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Content\CollectionFactory
    as EmailContentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class ContentRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentRepository implements EmailContentRepositoryInterface
{
    /**
     * @var EmailContentInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailContentInterfaceFactory
     */
    private $emailContentFactory;

    /**
     * @var EmailContentSearchResultsInterfaceFactory
     */
    private $emailContentSearchResultsFactory;

    /**
     * @var EmailContentCollectionFactory
     */
    private $emailContentCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param EntityManager $entityManager
     * @param EmailContentInterfaceFactory $emailContentFactory
     * @param EmailContentSearchResultsInterfaceFactory $emailContentSearchResultsFactory
     * @param EmailContentCollectionFactory $emailContentCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        EntityManager $entityManager,
        EmailContentInterfaceFactory $emailContentFactory,
        EmailContentSearchResultsInterfaceFactory $emailContentSearchResultsFactory,
        EmailContentCollectionFactory $emailContentCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper,
        CollectionProcessorInterface $collectionProcessor,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->entityManager = $entityManager;
        $this->emailContentFactory = $emailContentFactory;
        $this->emailContentSearchResultsFactory = $emailContentSearchResultsFactory;
        $this->emailContentCollectionFactory = $emailContentCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->collectionProcessor = $collectionProcessor;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EmailContentInterface $emailContent)
    {
        try {
            $this->entityManager->save($emailContent);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$emailContent->getId()]);
        return $this->get($emailContent->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($contentId)
    {
        if (!isset($this->instances[$contentId])) {
            /** @var EmailContentInterface $emailContent */
            $emailContent = $this->emailContentFactory->create();
            $this->entityManager->load($emailContent, $contentId);
            if (!$emailContent->getId()) {
                throw NoSuchEntityException::singleField('id', $contentId);
            }
            $this->instances[$contentId] = $emailContent;
        }
        return $this->instances[$contentId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EmailContentCollection $collection */
        $collection = $this->emailContentCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, EmailContentInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var EmailContentSearchResultsInterface $searchResults */
        $searchResults = $this->emailContentSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        $emailsContent = [];
        /** @var \Aheadworks\Followupemail2\Model\EmailContent $emailContentModel */
        foreach ($collection->getItems() as $item) {
            $emailsContent[] = $this->getDataObject($item);
        }
        $searchResults->setItems($emailsContent);

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EmailContentInterface $contentId)
    {
        return $this->deleteById($contentId->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($contentId)
    {
        /** @var EmailContentInterface $emailContent */
        $emailContent = $this->emailContentFactory->create();
        $this->entityManager->load($emailContent, $contentId);
        if (!$emailContent->getId()) {
            throw NoSuchEntityException::singleField('id', $contentId);
        }
        $this->entityManager->delete($emailContent);
        unset($this->instances[$contentId]);
        return true;
    }

    /**
     * Retrieves data object using model
     *
     * @param Aheadworks\Followupemail2\Model\EmailContent $model
     * @return EmailContentInterface
     */
    private function getDataObject($model)
    {
        /** @var EmailContentInterface $emailContent */
        $emailContent = $this->emailContentFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $emailContent,
            $this->dataObjectProcessor->buildOutputDataArray($model, EmailContentInterface::class),
            EmailContentInterface::class
        );
        return $emailContent;
    }
}
