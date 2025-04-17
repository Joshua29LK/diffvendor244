<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Magento\Backend\Block\Template\Context;

/**
 * Class Statistics
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Event\Email
 */
class Statistics extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Followupemail2::event/email/statistics.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_followupemail2_event_email_statistics';

    /**
     * @var int|null
     */
    private $emailId;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var array
     */
    private $statisticsData;

    /**
     * @param Context $context
     * @param EmailRepositoryInterface $emailRepository
     * @param EmailManagementInterface $emailManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        EmailRepositoryInterface $emailRepository,
        EmailManagementInterface $emailManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->emailRepository = $emailRepository;
        $this->emailManagement = $emailManagement;
    }

    /**
     * Get email content statistics data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEmailContentStatisticsData()
    {
        if (!$this->statisticsData) {
            $this->statisticsData = [];

            $emailId = $this->getEmailId();
            if ($emailId) {
                /** @var EmailInterface $email */
                $email = $this->emailRepository->get($emailId);
                $activeContent = false;
                if (!$email->getAbTestingMode()) {
                    $activeContent = $email->getPrimaryEmailContent();
                }
                /** @var EmailContentInterface[] $contentData */
                $contentData = $email->getContent();
                $contentVersion = EmailInterface::CONTENT_VERSION_A;
                /** @var EmailContentInterface $content */
                foreach ($contentData as $content) {
                    /** @var StatisticsInterface $statistics */
                    $statistics = $this->emailManagement->getStatisticsByContentId($content->getId());
                    $contentStatistics['version'] = $content->getId();
                    $contentStatistics['sent'] = $statistics->getSent();
                    $contentStatistics['opened'] = $statistics->getOpened();
                    $contentStatistics['clicks'] = $statistics->getClicked();
                    $contentStatistics['open_rate'] = $statistics->getOpenRate();
                    $contentStatistics['click_rate'] = $statistics->getClickRate();
                    if ($activeContent && $activeContent != $contentVersion) {
                        $contentStatistics['inactive'] = true;
                    } else {
                        $contentStatistics['inactive'] = false;
                    }
                    $this->statisticsData[] = $contentStatistics;
                    $contentVersion++;
                }
            }
        }
        return $this->statisticsData;
    }

    /**
     * Get reset url
     *
     * @return string
     */
    public function getResetUrl()
    {
        return $this->getUrl(
            'aw_followupemail2/event_email/resetStatistics/',
            [
                '_current' => true,
                '_secure' => $this->templateContext->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Get email id
     *
     * @return int|null
     */
    public function getEmailId()
    {
        if (!$this->emailId) {
            $this->emailId = (int)$this->getRequest()->getParam('id');
        }
        return $this->emailId;
    }

    /**
     * Set email id
     *
     * @param $emailId
     * @return Statistics
     */
    public function setEmailId($emailId)
    {
        $this->emailId = (int)$emailId;
        return $this;
    }
}
