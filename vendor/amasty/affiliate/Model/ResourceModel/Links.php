<?php

namespace Amasty\Affiliate\Model\ResourceModel;

class Links extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_links', 'link_id');
    }
}
