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
use Magento\Customer\Model\Visitor;
use Magento\Customer\Model\ResourceModel\Visitor\Collection as VisitorCollection;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory as VisitorCollectionFactory;

/**
 * Class CustomerLastActivity
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class CustomerLastActivity extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_CUSTOMER_LAST_ACTIVITY;

    /**
     * @var string
     */
    protected $referenceDataKey = 'visitor_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'visitor';

    /**
     * @var VisitorCollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param VisitorCollectionFactory $visitorCollectionFactory
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        VisitorCollectionFactory $visitorCollectionFactory
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
        $this->visitorCollectionFactory = $visitorCollectionFactory;
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Visitor|null
     */
    public function getEventObject($eventData)
    {
        /** @var VisitorCollection $collection */
        $collection = $this->visitorCollectionFactory->create();
        $collection->addFilter($this->getReferenceDataKey(), $eventData[$this->getReferenceDataKey()]);
        /** @var Visitor $visitor */
        $visitor = $collection->getFirstItem();

        if (!$visitor->getId()) {
            return null;
        }

        return $visitor;
    }
}
