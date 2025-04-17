<?php
namespace Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails;

use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails as ScheduledEmailsIndexerResource;

/**
 * Class AbstractAction
 * @package Aheadworks\Blog\Model\Indexer\ProductPost
 */
abstract class AbstractAction
{
    /**
     * @var ScheduledEmailsIndexerResource
     */
    protected $scheduledEmailsIndexerResource;

    /**
     * @param ScheduledEmailsIndexerResource $scheduledEmailsIndexerResource
     */
    public function __construct(
        ScheduledEmailsIndexerResource $scheduledEmailsIndexerResource
    ) {
        $this->scheduledEmailsIndexerResource = $scheduledEmailsIndexerResource;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);
}
