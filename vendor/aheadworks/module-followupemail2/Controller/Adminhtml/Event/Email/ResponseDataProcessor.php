<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Source\Email\Status as EmailStatusSource;
use Aheadworks\Followupemail2\Ui\DataProvider\Event\ManageFormProcessor;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class ResponseDataProcessor
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class ResponseDataProcessor
{
    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var EmailStatusSource
     */
    private $emailStatusSource;

    /**
     * @var ManageFormProcessor
     */
    private $manageFormProcessor;

    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param EmailManagementInterface $emailManagement
     * @param EmailStatusSource $emailStatusSource
     * @param ManageFormProcessor $manageFormProcessor
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param StatisticsManagementInterface $statisticsManagement
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        EmailManagementInterface $emailManagement,
        EmailStatusSource $emailStatusSource,
        ManageFormProcessor $manageFormProcessor,
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        StatisticsManagementInterface $statisticsManagement,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->emailManagement = $emailManagement;
        $this->emailStatusSource = $emailStatusSource;
        $this->manageFormProcessor = $manageFormProcessor;
        $this->campaignManagement = $campaignManagement;
        $this->eventRepository = $eventRepository;
        $this->statisticsManagement = $statisticsManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Get prepared data
     *
     * @param int $eventId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPreparedData($eventId)
    {
        /** @var EventInterface $event */
        $event = $this->eventRepository->get($eventId);

        $emails = [];
        /** @var EmailInterface[] $eventEmails */
        $eventEmails =  $this->emailManagement->getEmailsByEventId($eventId);
        foreach ($eventEmails as $eventEmail) {
            $eventEmailData = $this->getData($eventEmail);
            $emails[] = $eventEmailData;
        }

        $preparedData = [
            'emails' => $emails
        ];

        $preparedData = $this->addStatisticsData($event->getCampaignId(), $preparedData);
        $preparedData = $this->addEventTotals($eventId, $preparedData);

        return $preparedData;
    }

    /**
     * Get response data
     *
     * @param EmailInterface $email
     * @return array
     */
    public function getData($email)
    {
        $emailData = $this->dataObjectProcessor->buildOutputDataArray(
            $email,
            EmailInterface::class
        );

        /** @var StatisticsInterface $emailStatistics */
        $emailStatistics = $this->emailManagement->getStatistics($email);
        $emailData['when'] = $this->manageFormProcessor->getWhen($email);
        $emailData['sent'] = $emailStatistics->getSent();
        $emailData['opened'] = $emailStatistics->getOpened();
        $emailData['clicks'] = $emailStatistics->getClicked();
        $emailData['open_rate'] = $emailStatistics->getOpenRate();
        $emailData['click_rate'] = $emailStatistics->getClickRate();
        $emailData['status'] = $this->emailStatusSource->getOptionByValue($email->getStatus());
        $emailData['is_email_disabled'] = ($email->getStatus() == EmailInterface::STATUS_DISABLED);

        return $emailData;
    }

    /**
     * Add statistics data
     *
     * @param int $campaignId
     * @param array $data
     * @return array
     */
    public function addStatisticsData($campaignId, $data)
    {
        $eventsCount = 0;
        $emailsCount = 0;
        $campaignStatsData = [];
        if ($campaignId) {
            $eventsCount = $this->campaignManagement->getEventsCount($campaignId);
            $emailsCount = $this->campaignManagement->getEmailsCount($campaignId);

            $campaignStats = $this->statisticsManagement->getByCampaignId($campaignId);
            $campaignStatsData = $this->dataObjectProcessor->buildOutputDataArray(
                $campaignStats,
                StatisticsInterface::class
            );
        }

        $data = array_merge($data, [
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => $campaignStatsData
        ]);

        return $data;
    }

    /**
     * Add event email totals
     *
     * @param int $eventId
     * @param array $data
     * @return array
     */
    public function addEventTotals($eventId, $data)
    {
        $data = array_merge($data, [
            'totals' => $this->manageFormProcessor->getEventTotals($eventId),
        ]);

        return $data;
    }
}
