<?php
namespace Aheadworks\Followupemail2\Cron;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class EventQueueProcessor
 * @package Aheadworks\Followupemail2\Cron
 */
class EventQueueProcessor extends CronAbstract
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
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param EventQueueManagementInterface $eventQueueManagement
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        EventQueueManagementInterface $eventQueueManagement
    ) {
        $this->config = $config;
        $this->eventQueueManagement = $eventQueueManagement;
        parent::__construct($dateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->config->isEnabled()
            || $this->isLocked($this->config->getProcessEventQueueLastExecTime(), self::RUN_INTERVAL)
        ) {
            return $this;
        }
        $this->eventQueueManagement->processUnprocessedItems(self::ITEMS_PER_RUN);

        $this->config->setProcessEventQueueLastExecTime($this->getCurrentTime());
        return $this;
    }
}
