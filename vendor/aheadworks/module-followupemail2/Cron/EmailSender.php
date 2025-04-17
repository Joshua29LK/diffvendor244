<?php
namespace Aheadworks\Followupemail2\Cron;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class EmailSender
 * @package Aheadworks\Followupemail2\Cron
 */
class EmailSender extends CronAbstract
{
    /**
     * Cron run interval in seconds
     */
    const RUN_INTERVAL = 240;

    /**
     * Event queue items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param QueueManagementInterface $queueManagement
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        QueueManagementInterface $queueManagement
    ) {
        $this->config = $config;
        $this->queueManagement = $queueManagement;
        parent::__construct($dateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->config->isEnabled()
            || $this->isLocked($this->config->getSendEmailsLastExecTime(), self::RUN_INTERVAL)
        ) {
            return $this;
        }
        $this->queueManagement->sendScheduledEmails(self::ITEMS_PER_RUN);

        $this->config->setSendEmailsLastExecTime($this->getCurrentTime());
        return $this;
    }
}
