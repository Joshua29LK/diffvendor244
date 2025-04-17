<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class AbstractHandler
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = '';

    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventHistoryRepositoryInterface
     */
    protected $eventHistoryRepository;

    /**
     * @var EventQueueManagementInterface
     */
    protected $eventQueueManagement;

    /**
     * @var EventValidator
     */
    protected $eventValidator;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer
    ) {
        $this->campaignManagement = $campaignManagement;
        $this->eventRepository = $eventRepository;
        $this->eventHistoryRepository = $eventHistoryRepository;
        $this->eventValidator = $eventValidator;
        $this->eventQueueManagement = $eventQueueManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceDataKey()
    {
        return $this->referenceDataKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateEventData(array $data = [])
    {
        $dataKeysRequired = ['email', 'store_id', 'customer_group_id', 'customer_name'];
        foreach ($dataKeysRequired as $dataKey) {
            if (!array_key_exists($dataKey, $data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        /** @var EventInterface[] $events */
        $events = $this->validate($eventHistoryItem);

        if (count($events) > 0) {
            /** @var EventInterface $event */
            foreach ($events as $event) {
                /** @var EventQueueInterface $queueItem */
                $this->eventQueueManagement->add($event, $eventHistoryItem);
            }
            $eventHistoryItem->setProcessed(true);
            $this->eventHistoryRepository->save($eventHistoryItem);
        } else {
            $this->eventHistoryRepository->delete($eventHistoryItem);
        }
    }

    /**
     * Validate specified event history item
     *
     * @param EventHistoryInterface $eventHistoryItem
     * @return EventSearchResultsInterface[]
     */
    protected function validate(EventHistoryInterface $eventHistoryItem)
    {
        $events = [];
        $eventData = $this->serializer->unserialize($eventHistoryItem->getEventData());
        $eventObject = $this->getEventObject($eventData);

        /** @var EventInterface $event */
        foreach ($this->getEventsForValidation($eventHistoryItem->getEventType()) as $event) {
            if ($this->eventValidator->validate($event, $eventData, $eventObject)) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Get events for validation
     *
     * @param string $eventTypeCode
     * @return EventInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEventsForValidation($eventTypeCode)
    {
        $campaigns = $this->campaignManagement->getActiveCampaigns();
        $campaignIds = [];
        /** @var CampaignInterface $campaign */
        foreach ($campaigns as $campaign) {
            $campaignIds[] = $campaign->getId();
        }

        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignIds, 'in')
            ->addFilter(EventInterface::EVENT_TYPE, $eventTypeCode)
            ->addFilter(EventInterface::STATUS, EventInterface::STATUS_ENABLED);

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        return $result->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEvents($eventCode, $data = [])
    {
        $this->eventQueueManagement->cancelEvents(
            $eventCode,
            $data[$this->getReferenceDataKey()]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEventObjectVariableName()
    {
        return $this->eventObjectVariableName;
    }
}
