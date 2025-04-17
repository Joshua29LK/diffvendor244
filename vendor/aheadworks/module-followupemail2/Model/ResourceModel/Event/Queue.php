<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Queue
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event
 * @codeCoverageIgnore
 */
class Queue extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_event_queue', 'id');
    }
}
