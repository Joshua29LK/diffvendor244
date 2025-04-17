<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Condition\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ConditionStoreCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Condition\ConditionStore::class,
            \Amasty\Rma\Model\Condition\ResourceModel\ConditionStore::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
