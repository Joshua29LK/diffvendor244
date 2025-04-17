<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;

/**
 * Class CampaignManagement
 * @package Aheadworks\Followupemail2\Model
 */
class CampaignManagement implements CampaignManagementInterface
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventRepositoryInterface $eventRepository
     * @param EventManagementInterface $eventManagement
     * @param EmailRepositoryInterface $emailRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EventTypePool $eventTypePool
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        EventRepositoryInterface $eventRepository,
        EventManagementInterface $eventManagement,
        EmailRepositoryInterface $emailRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EventTypePool $eventTypePool
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->eventManagement = $eventManagement;
        $this->emailRepository = $emailRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eventTypePool = $eventTypePool;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveCampaigns()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $currentDateTime = $now->format(StdlibDateTime::DATETIME_PHP_FORMAT);

        $this->searchCriteriaBuilder
            ->addFilter(CampaignInterface::STATUS, CampaignInterface::STATUS_ENABLED)
            ->addFilter(CampaignInterface::START_DATE, $currentDateTime)
            ->addFilter(CampaignInterface::END_DATE, $currentDateTime);

        /** @var CampaignSearchResultsInterface $result */
        $result = $this->campaignRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        return $result->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function duplicateCampaignEvents($sourceCampaignId, $destinationCampaignId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $sourceCampaignId, 'eq');

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        /** @var EventInterface $event */
        foreach ($result->getItems() as $event) {
            /** @var EventInterface $eventDataObject */
            $eventDataObject = $this->eventRepository->get($event->getId());
            $eventDataObject->setId(null);
            $eventDataObject->setCampaignId($destinationCampaignId);
            $eventDataObject = $this->eventRepository->save($eventDataObject);

            $this->eventManagement->duplicateEventEmails($event->getId(), $eventDataObject->getId());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEventName($campaignId, $eventType)
    {
        $eventTypeTitle = $this->eventTypePool->getType($eventType)->getTitle();

        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignId, 'eq');

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $i = 1;
        $eventName = $eventTypeTitle;
        do {
            $uniqueName = true;
            /** @var EventInterface $event */
            foreach ($result->getItems() as $event) {
                if ($event->getName() == $eventName) {
                    $uniqueName = false;
                }
            }
            if (!$uniqueName) {
                $eventName = $eventTypeTitle . ' #' . $i;
                $i++;
            }
        } while (!$uniqueName);

        return $eventName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsCount($campaignId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignId, 'eq');

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList($this->searchCriteriaBuilder->create());

        return $result->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailsCount($campaignId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignId, 'eq');
        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList($this->searchCriteriaBuilder->create());
        if ($result->getTotalCount() > 0) {
            $eventIds = [];
            /** @var EventInterface $item */
            foreach ($result->getItems() as $item) {
                $eventIds[] = $item->getId();
            }

            $this->searchCriteriaBuilder
                ->addFilter(EmailInterface::EVENT_ID, $eventIds, 'in');
            /** @var EmailSearchResultsInterface $result */
            $result = $this->emailRepository->getList($this->searchCriteriaBuilder->create());

            return $result->getTotalCount();
        }
        return 0;
    }
}
