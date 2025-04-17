<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\EventHistory;

use Aheadworks\Followupemail2\Model\EventHistory;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory as EventHistoryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\EventHistory
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EventHistory::class, EventHistoryResource::class);
    }
}
