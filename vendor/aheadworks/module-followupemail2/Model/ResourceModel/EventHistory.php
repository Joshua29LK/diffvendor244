<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class EventHistory
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @codeCoverageIgnore
 */
class EventHistory extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_event_history', 'id');
    }
}
