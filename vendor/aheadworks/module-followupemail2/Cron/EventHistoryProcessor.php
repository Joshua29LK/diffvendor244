<?php
namespace Aheadworks\Followupemail2\Cron;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class EventHistoryProcessor
 * @package Aheadworks\Followupemail2\Cron
 */
class EventHistoryProcessor extends CronAbstract
{
    /**
     * Cron run interval in seconds
     */
    const RUN_INTERVAL = 240;

    /**
     * Event history items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var EventHistoryManagementInterface
     */
    private $eventHistoryManagement;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param EventHistoryManagementInterface $eventHistoryManagement
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        EventHistoryManagementInterface $eventHistoryManagement
    ) {
        $this->config = $config;
        $this->eventHistoryManagement = $eventHistoryManagement;
        parent::__construct($dateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->config->isEnabled()
            || $this->isLocked($this->config->getProcessEventHistoryLastExecTime(), self::RUN_INTERVAL)
        ) {
            return $this;
        }
        $this->eventHistoryManagement->processUnprocessedItems(self::ITEMS_PER_RUN);

        $this->config->setProcessEventHistoryLastExecTime($this->getCurrentTime());
        return $this;
    }
}
