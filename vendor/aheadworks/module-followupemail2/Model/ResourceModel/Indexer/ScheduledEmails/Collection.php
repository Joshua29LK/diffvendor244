<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails;

use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails as ScheduledEmailsResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DataObject;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'event_queue_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(DataObject::class, ScheduledEmailsResource::class);
    }
}
