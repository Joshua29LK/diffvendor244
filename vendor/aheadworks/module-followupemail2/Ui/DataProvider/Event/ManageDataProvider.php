<?php
namespace Aheadworks\Followupemail2\Ui\DataProvider\Event;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\Filter;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ManageDataProvider
 * @package Aheadworks\Followupemail2\Ui\DataProvider\Event
 * @codeCoverageIgnore
 */
class ManageDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var ManageFormProcessor
     */
    private $manageFormProcessor;

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * ManageDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventRepositoryInterface $eventRepository
     * @param EmailRepositoryInterface $emailRepository
     * @param EmailManagementInterface $emailManagement
     * @param ManageFormProcessor $manageFormProcessor
     * @param EventTypePool $eventTypePool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param RequestInterface $request
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CampaignRepositoryInterface $campaignRepository,
        EventRepositoryInterface $eventRepository,
        EmailRepositoryInterface $emailRepository,
        EmailManagementInterface $emailManagement,
        ManageFormProcessor $manageFormProcessor,
        EventTypePool $eventTypePool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        RequestInterface $request,
        DataObjectProcessor $dataObjectProcessor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->emailRepository = $emailRepository;
        $this->emailManagement = $emailManagement;
        $this->manageFormProcessor = $manageFormProcessor;
        $this->eventTypePool = $eventTypePool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->request = $request;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $id = $this->request->getParam($this->getRequestFieldName());
        $data = [];
        if ($id) {
            try {
                /** @var CampaignInterface $campaign */
                $campaign = $this->campaignRepository->get($id);
                $campaignData = $this->dataObjectProcessor->buildOutputDataArray(
                    $campaign,
                    CampaignInterface::class
                );
                $formData['campaign'] = $campaignData;
                $formData['campaign_id'] = (string)$id;
                $formData['campaign_statuses'] = $this->manageFormProcessor->getCampaignStatuses();

                $this->sortOrderBuilder
                    ->setField(EventInterface::ID)
                    ->setDescendingDirection();

                $this->searchCriteriaBuilder
                    ->addFilter(
                        EventInterface::CAMPAIGN_ID,
                        $id,
                        'eq'
                    )
                    ->addSortOrder($this->sortOrderBuilder->create());

                /** @var EventSearchResultsInterface $eventsResult */
                $eventsResult = $this->eventRepository->getList($this->searchCriteriaBuilder->create());

                if ($eventsResult->getTotalCount() > 0) {
                    $eventIndex = 0;
                    /** @var EventInterface $event */
                    foreach ($eventsResult->getItems() as $event) {
                        $eventData = $this->dataObjectProcessor->buildOutputDataArray(
                            $event,
                            EventInterface::class
                        );
                        $eventData['record_id'] = $eventIndex;

                        /** @var TypeInterface $eventType */
                        $eventType = $this->eventTypePool->getType($event->getEventType());
                        $eventData['event_type_label'] = $eventType->getTitle();
                        $eventData['emails'] = $this->manageFormProcessor->getEventEmailsData($event->getId());
                        $eventData['totals'] = $this->manageFormProcessor->getEventTotals($event->getId());

                        $formData['events'][] = $eventData;
                        $eventIndex++;
                    }
                }
                $data[$id] = $formData;
            } catch (NoSuchEntityException $e) {
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }
}
