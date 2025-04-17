<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Collection as EmailCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\CollectionFactory as EmailCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class EmailRepository
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailRepository implements EmailRepositoryInterface
{
    /**
     * @var EmailInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailInterfaceFactory
     */
    private $emailFactory;

    /**
     * @var EmailSearchResultsInterfaceFactory
     */
    private $emailSearchResultsFactory;

    /**
     * @var EmailCollectionFactory
     */
    private $emailCollectionFactory;

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
     * @param EmailInterfaceFactory $emailFactory
     * @param EmailSearchResultsInterfaceFactory $emailSearchResultsFactory
     * @param EmailCollectionFactory $emailCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        EntityManager $entityManager,
        EmailInterfaceFactory $emailFactory,
        EmailSearchResultsInterfaceFactory $emailSearchResultsFactory,
        EmailCollectionFactory $emailCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->entityManager = $entityManager;
        $this->emailFactory = $emailFactory;
        $this->emailSearchResultsFactory = $emailSearchResultsFactory;
        $this->emailCollectionFactory = $emailCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EmailInterface $email)
    {
        try {
            $this->entityManager->save($email);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$email->getId()]);
        return $this->get($email->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($emailId)
    {
        if (!isset($this->instances[$emailId])) {
            /** @var EmailInterface $email */
            $email = $this->emailFactory->create();
            $this->entityManager->load($email, $emailId);
            if (!$email->getId()) {
                throw NoSuchEntityException::singleField('id', $emailId);
            }
            $this->instances[$emailId] = $email;
        }
        return $this->instances[$emailId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EmailSearchResultsInterface $searchResults */
        $searchResults = $this->emailSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var EmailCollection $collection */
        $collection = $this->emailCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, EmailInterface::class);

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

        $emails = [];
        /** @var \Aheadworks\Followupemail2\Model\Email $emailModel */
        foreach ($collection as $emailModel) {
            /** @var EmailInterface $email */
            $email = $this->emailFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $email,
                $emailModel->getData(),
                EmailInterface::class
            );
            $emails[] = $email;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($emails)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EmailInterface $email)
    {
        return $this->deleteById($email->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($emailId)
    {
        /** @var EmailInterface $email */
        $email = $this->emailFactory->create();
        $this->entityManager->load($email, $emailId);
        if (!$email->getId()) {
            throw NoSuchEntityException::singleField('id', $emailId);
        }
        $this->entityManager->delete($email);
        unset($this->instances[$emailId]);
        return true;
    }
}
