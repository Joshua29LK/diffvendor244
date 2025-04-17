<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class CustomerRegistration
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class CustomerRegistration extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_CUSTOMER_REGISTRATION;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'customer';

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        parent::__construct(
            $campaignManagement,
            $eventRepository,
            $eventHistoryRepository,
            $eventValidator,
            $eventQueueManagement,
            $searchCriteriaBuilder,
            $serializer
        );
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Customer|null
     */
    public function getEventObject($eventData)
    {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->addFilter($this->getReferenceDataKey(), $eventData[$this->getReferenceDataKey()]);
        $customer = $collection->getFirstItem();

        if (!$customer->getId()) {
            return null;
        }

        return $customer;
    }
}
