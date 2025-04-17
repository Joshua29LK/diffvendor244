<?php
namespace Aheadworks\Followupemail2\Plugin\Model\ResourceModel\Event;

use Magento\Framework\Indexer\StateInterface;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Processor as ScheduledEmailsProcessor;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Processor as ScheduledEmailsIndexer;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\QueueRepository as Subject;

/**
 * Class QueueRepositoryPlugin
 * @package Aheadworks\Blog\Model\Plugin
 */
class QueueRepositoryPlugin
{
    /**
     * @var StateInterface
     */
    private $indexerState;

    /**
     * @var ScheduledEmailsIndexer
     */
    private $scheduledEmailsIndexer;

    /**
     * @param StateInterface $indexerState
     * @param ScheduledEmailsIndexer $scheduledEmailsIndexer
     */
    public function __construct(
        StateInterface $indexerState,
        ScheduledEmailsIndexer $scheduledEmailsIndexer
    ) {
        $this->indexerState = $indexerState;
        $this->scheduledEmailsIndexer = $scheduledEmailsIndexer;
    }

    /**
     * Set new index state after event queue save
     *
     * @param Subject $subject
     * @param EventQueueInterface $eventQueue
     * @return EventQueueInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($subject, $eventQueue)
    {
        if (\is_object($eventQueue)
            && $this->scheduledEmailsIndexer->isIndexerScheduled()
            && $eventQueue instanceof EventQueueInterface
        ) {
            $this->indexerState->loadByIndexer(ScheduledEmailsProcessor::INDEXER_ID);
            $this->indexerState->setStatus(StateInterface::STATUS_INVALID);
            $this->indexerState->save();
        }
        return $eventQueue;
    }
}
