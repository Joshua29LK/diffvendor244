<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Unsubscribe
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Unsubscribe extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_unsubscribe', 'id');
    }
}
