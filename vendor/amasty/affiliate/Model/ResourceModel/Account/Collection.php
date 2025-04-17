<?php

namespace Amasty\Affiliate\Model\ResourceModel\Account;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var string $_idFieldName */
    protected $_idFieldName = 'account_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Account', 'Amasty\Affiliate\Model\ResourceModel\Account');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = main_table.customer_id'
            )
            ->distinct();

        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function addCodeFilter($code)
    {
        $this->addFieldToFilter('referring_code', ['eq' => $code]);

        return $this;
    }
}
