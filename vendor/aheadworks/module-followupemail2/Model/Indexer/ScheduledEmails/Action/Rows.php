<?php
namespace Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\AbstractAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;

/**
 * Class Rows
 * @package Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action
 */
class Rows extends AbstractAction
{
    /**
     * Execute Rows reindex
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            throw new InputException(__('Bad value was supplied.'));
        }
        try {
            $this->scheduledEmailsIndexerResource->reindexRows($ids);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
