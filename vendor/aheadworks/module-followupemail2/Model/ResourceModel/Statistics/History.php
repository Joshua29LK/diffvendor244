<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Statistics;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class History
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Statistics
 * @codeCoverageIgnore
 */
class History extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_statistics_history', 'id');
    }
}
