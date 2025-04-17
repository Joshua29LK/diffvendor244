<?php

namespace Amasty\Affiliate\Model\ResourceModel\Banner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'banner_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Banner', 'Amasty\Affiliate\Model\ResourceModel\Banner');
    }

    /**
     * @param int $status
     * @return $this
     */
    public function addStatusFilter($status = 1)
    {
        $this->addFieldToFilter('status', ['eq' => $status]);

        return $this;
    }
}
