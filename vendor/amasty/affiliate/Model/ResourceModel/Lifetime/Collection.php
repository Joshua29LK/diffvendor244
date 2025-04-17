<?php

namespace Amasty\Affiliate\Model\ResourceModel\Lifetime;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'lifetime_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Lifetime', 'Amasty\Affiliate\Model\ResourceModel\Lifetime');
    }
}
