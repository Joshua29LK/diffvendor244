<?php

namespace Amasty\Affiliate\Model\ResourceModel;

class Lifetime extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_lifetime', 'lifetime_id');
    }
}
