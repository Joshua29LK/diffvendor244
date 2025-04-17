<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Campaign
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Campaign extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_campaign', 'id');
    }
}
