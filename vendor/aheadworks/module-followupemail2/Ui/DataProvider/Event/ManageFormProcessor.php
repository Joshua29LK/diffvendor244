<?php
namespace Aheadworks\Followupemail2\Ui\DataProvider\Event;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Source\Email\Status as EmailStatusSource;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class ManageFormProcessor
 * @package Aheadworks\Followupemail2\Ui\DataProvider\Event
 * @codeCoverageIgnore
 */
class ManageFormProcessor
{
    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var EmailStatusSource
     */
    private $emailStatusSource;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param EmailManagementInterface $emailManagement
     * @param StatisticsManagementInterface $statisticsManagement
     * @param EmailStatusSource $emailStatusSource
     * @param CampaignRepositoryInterface $campaignRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        EmailManagementInterface $emailManagement,
        StatisticsManagementInterface $statisticsManagement,
        EmailStatusSource $emailStatusSource,
        CampaignRepositoryInterface $campaignRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->emailManagement = $emailManagement;
        $this->statisticsManagement = $statisticsManagement;
        $this->emailStatusSource = $emailStatusSource;
        $this->campaignRepository = $campaignRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Get event totals
     *
     * @param int $eventId
     * @return array
     */
    public function getEventTotals($eventId)
    {
        /** @var StatisticsInterface $eventStatistics */
        $eventStatistics = $this->statisticsManagement->getByEventId($eventId);
        $eventTotals = [];
        $eventTotals['sent'] = $eventStatistics->getSent();
        $eventTotals['opened'] = $eventStatistics->getOpened();
        $eventTotals['clicks'] = $eventStatistics->getClicked();
        $eventTotals['open_rate'] = $eventStatistics->getOpenRate();
        $eventTotals['click_rate'] = $eventStatistics->getClickRate();

        return $eventTotals;
    }

    /**
     * Get emails for form
     *
     * @param int $eventId
     * @return array
     */
    public function getEventEmailsData($eventId)
    {
        $emailsData = [];

        /** @var EmailInterface[] $emails */
        $emails = $this->emailManagement->getEmailsByEventId($eventId);

        $emailsIndex = 0;
        /** @var EmailInterface $email */
        foreach ($emails as $email) {
            $emailData = $this->dataObjectProcessor->buildOutputDataArray(
                $email,
                EmailInterface::class
            );

            /** @var StatisticsInterface $emailStatistics */
            $emailStatistics = $this->emailManagement->getStatistics($email);
            $emailData['record_id'] = $emailsIndex;
            $emailData['when'] = $this->getWhen($email);
            $emailData['sent'] = $emailStatistics->getSent();
            $emailData['opened'] = $emailStatistics->getOpened();
            $emailData['clicks'] = $emailStatistics->getClicked();
            $emailData['open_rate'] = $emailStatistics->getOpenRate();
            $emailData['click_rate'] = $emailStatistics->getClickRate();
            $emailData['status'] = $this->emailStatusSource->getOptionByValue($email->getStatus());
            $emailData['is_email_disabled'] = ($email->getStatus() == EmailInterface::STATUS_DISABLED);

            $emailsData[] = $emailData;
            $emailsIndex++;
        }

        return $emailsData;
    }

    /**
     * Get when string
     *
     * @param EmailInterface $email
     * @return \Magento\Framework\Phrase|string
     */
    public function getWhen($email)
    {
        if ($email->getStatus() == EmailInterface::STATUS_DISABLED) {
            return __('Never');
        }
        if ($email->getEmailSendDays() > 0) {
            $days = sprintf(
                "%s %s ",
                $email->getEmailSendDays(),
                $email->getEmailSendDays() > 1 ? __('days') : __('day')
            );
        } else {
            $days = '';
        }
        if ($email->getEmailSendHours() > 0) {
            $hours = sprintf(
                "%s %s ",
                $email->getEmailSendHours(),
                $email->getEmailSendHours() > 1 ? __('hours') : __('hour')
            );
        } else {
            $hours = '';
        }
        if ($email->getEmailSendMinutes() > 0) {
            $minutes = sprintf(
                "%s %s ",
                $email->getEmailSendMinutes(),
                __('minutes')
            );
        } else {
            $minutes = '';
        }
        $when = $days . $hours . $minutes;

        if ($when == '') {
            $when = __('Immediately ');
        }

        if ($this->emailManagement->isFirst($email->getId(), $email->getEventId())) {
            if ($email->getWhen() == EmailInterface::WHEN_BEFORE) {
                $when .= __('before event triggered');
            } else {
                $when .= __('after event triggered');
            }
        } else {
            $when .= __('after previous email sent');
        }

        return $when;
    }

    /**
     * Get campaign statuses
     *
     * @return array
     */
    public function getCampaignStatuses()
    {
        $campaignStatuses = [];

        try {
            /** @var CampaignInterface[] $campaigns */
            $campaigns = $this->campaignRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();

            foreach ($campaigns as $campaign) {
                $campaignStatuses[$campaign->getId()] = $campaign->getStatus() == CampaignInterface::STATUS_DISABLED;
            }
        } catch (LocalizedException $e) {
            // do nothing
        }

        return $campaignStatuses;
    }
}
