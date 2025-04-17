<?php
namespace Aheadworks\Followupemail2\Cron;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface as EventHandlerInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class BirthdaysProcessor
 * @package Aheadworks\Followupemail2\Cron
 */
class BirthdaysProcessor extends CronAbstract
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
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var EventHistoryInterfaceFactory
     */
    private $eventHistoryItemFactory;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param EventTypePool $eventTypePool
     * @param EventHistoryInterfaceFactory $eventHistoryItemFactory
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        EventTypePool $eventTypePool,
        EventHistoryInterfaceFactory $eventHistoryItemFactory
    ) {
        $this->config = $config;
        $this->eventTypePool = $eventTypePool;
        $this->eventHistoryItemFactory = $eventHistoryItemFactory;
        parent::__construct($dateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->config->isEnabled()
            || $this->isLocked($this->config->getProcessBirthdaysLastExecTime(), self::RUN_INTERVAL)
        ) {
            return $this;
        }

        try {
            /** @var EventTypeInterface $customerBirthdayType */
            $customerBirthdayType = $this->eventTypePool->getType(EventInterface::TYPE_CUSTOMER_BIRTHDAY);
            if ($customerBirthdayType->isEnabled()) {
                /** @var EventHandlerInterface $handler */
                $handler = $customerBirthdayType->getHandler();
                if ($handler) {
                    $fakeEventHistoryItem = $this->eventHistoryItemFactory->create();
                    $handler->process($fakeEventHistoryItem);
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }

        $this->config->setProcessBirthdaysLastExecTime($this->getCurrentTime());
        return $this;
    }
}
