<?php
namespace Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Full
 * @package Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action
 */
class Full extends AbstractAction
{
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids = null)
    {
        try {
            $this->scheduledEmailsIndexerResource->reindexAll();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
