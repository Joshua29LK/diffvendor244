<?php
namespace Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\AbstractAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;

/**
 * Class Row
 * @package Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action
 */
class Row extends AbstractAction
{
    /**
     * Execute Row reindex
     *
     * @param int|null $id
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new InputException(__('We can\'t rebuild the index for an undefined entity.'));
        }
        try {
            $this->scheduledEmailsIndexerResource->reindexRows([$id]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
