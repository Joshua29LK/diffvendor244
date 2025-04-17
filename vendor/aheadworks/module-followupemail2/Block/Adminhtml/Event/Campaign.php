<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Campaign
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Event
 */
class Campaign extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Followupemail2::event/campaign.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_followupemail2_event_campaign';

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
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CampaignInterface
     */
    private $campaign;

    /**
     * @var int
     */
    private $eventsCount;

    /**
     * @var int
     */
    private $emailsCount;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventRepositoryInterface $eventRepository
     * @param EmailRepositoryInterface $emailRepository
     * @param StatisticsManagementInterface $statisticsManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CampaignRepositoryInterface $campaignRepository,
        EventRepositoryInterface $eventRepository,
        EmailRepositoryInterface $emailRepository,
        StatisticsManagementInterface $statisticsManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->emailRepository = $emailRepository;
        $this->statisticsManagement = $statisticsManagement;
    }

    /**
     * Get campaign name
     *
     * @return string
     */
    public function getName()
    {
        if ($campaign = $this->getCampaign()) {
            return $campaign->getName();
        }
        return '';
    }

    /**
     * Get campaign description
     *
     * @return string
     */
    public function getDescription()
    {
        if ($campaign = $this->getCampaign()) {
            return $campaign->getDescription();
        }
        return '';
    }

    /**
     * Has date selected
     *
     * @return bool
     */
    public function hasDateSelected()
    {
        if ($campaign = $this->getCampaign()) {
            return $campaign->getStartDate() || $campaign->getEndDate();
        }
        return false;
    }

    /**
     * Get start date
     *
     * @return null|string
     */
    public function getStartDate()
    {
        if ($campaign = $this->getCampaign()) {
            $unformattedDate = $campaign->getStartDate();
            if (!empty($unformattedDate)) {
                return $this->dateFormat($unformattedDate);
            }
        }
        return null;
    }

    /**
     * Get end date
     *
     * @return null|string
     */
    public function getEndDate()
    {
        if ($campaign = $this->getCampaign()) {
            $unformattedDate = $campaign->getEndDate();
            if (!empty($unformattedDate)) {
                return $this->dateFormat($unformattedDate);
            }
        }
        return null;
    }

    /**
     * Get campaign events count
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEventsCount()
    {
        $totals = $this->getTotals();
        return $totals['events_count'];
    }

    /**
     * Get campaign emails count
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEmailsCount()
    {
        $totals = $this->getTotals();
        return $totals['emails_count'];
    }

    /**
     * Get email statistics
     *
     * @return array
     */
    public function getEmailStatistics()
    {
        if ($campaign = $this->getCampaign()) {
            /** @var StatisticsInterface $statistics */
            $statistics = $this->statisticsManagement->getByCampaignId($campaign->getId());
            $result = [
                'sent'          => $statistics->getSent(),
                'opened'        => $statistics->getOpened(),
                'clicks'        => $statistics->getClicked(),
                'open_rate'     => $statistics->getOpenRate(),
                'click_rate'    => $statistics->getClickRate()
            ];
        } else {
            $result = [
                'sent'          => 0,
                'opened'        => 0,
                'clicks'        => 0,
                'open_rate'     => 0,
                'click_rate'    => 0
            ];
        }

        return $result;
    }

    /**
     * Format date in short format
     *
     * @param string $date
     * @return string
     */
    private function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM, false);
    }

    /**
     * Get campaign
     *
     * @return CampaignInterface|null
     */
    private function getCampaign()
    {
        if (!$this->campaign) {
            $campaignId = $this->getRequest()->getParam('campaign_id');
            if ($campaignId) {
                try {
                    $this->campaign = $this->campaignRepository->get($campaignId);
                } catch (NoSuchEntityException $e) {
                    $this->campaign = null;
                }
            } else {
                $this->campaign = null;
            }
        }
        return $this->campaign;
    }

    /**
     * Get campaign totals
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTotals()
    {
        if ($this->eventsCount == null || $this->emailsCount == null) {
            if ($campaign = $this->getCampaign()) {
                $this->searchCriteriaBuilder->addFilter(
                    EventInterface::CAMPAIGN_ID,
                    $campaign->getId(),
                    'eq'
                );
                /** @var EventSearchResultsInterface $eventsResult */
                $eventsResult = $this->eventRepository->getList($this->searchCriteriaBuilder->create());
                $this->eventsCount = $eventsResult->getTotalCount();
                $eventIds = [];
                /** @var EventInterface $event */
                foreach ($eventsResult->getItems() as $event) {
                    $eventIds[] = $event->getId();
                }

                $this->searchCriteriaBuilder->addFilter(
                    EmailInterface::EVENT_ID,
                    $eventIds,
                    'in'
                );
                /** @var EmailSearchResultsInterface $emailsResult */
                $emailsResult = $this->emailRepository->getList($this->searchCriteriaBuilder->create());
                $this->emailsCount = $emailsResult->getTotalCount();
            } else {
                $this->eventsCount = 0;
                $this->emailsCount = 0;
            }
        }
        return [
            'events_count' => $this->eventsCount,
            'emails_count' => $this->emailsCount
        ];
    }
}
