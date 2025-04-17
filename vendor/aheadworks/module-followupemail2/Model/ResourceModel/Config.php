<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

/**
 * Class Config
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_config', 'id');
    }
}
