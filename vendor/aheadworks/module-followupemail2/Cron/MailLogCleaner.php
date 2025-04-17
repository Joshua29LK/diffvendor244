<?php
namespace Aheadworks\Followupemail2\Cron;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class MailLogCleaner
 * @package Aheadworks\Followupemail2\Cron
 */
class MailLogCleaner extends CronAbstract
{
    /**
     * Cron run interval in seconds
     */
    const RUN_INTERVAL = 86000;

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
            || $this->isLocked($this->config->getClearLogLastExecTime(), self::RUN_INTERVAL)
        ) {
            return $this;
        }
        $this->clearMailLog();

        $this->config->setClearLogLastExecTime($this->getCurrentTime());
        return $this;
    }

    /**
     * Clear mail log
     *
     * @return MailLogCleaner
     */
    private function clearMailLog()
    {
        $keepEmailsFor = $this->config->getKeepEmailsFor();
        if (!$keepEmailsFor) {
            return $this;
        }
        $this->queueManagement->clearQueue($keepEmailsFor);

        return $this;
    }
}
