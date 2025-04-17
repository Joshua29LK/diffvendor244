<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Reason\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ReasonStoreCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Reason\ReasonStore::class,
            \Amasty\Rma\Model\Reason\ResourceModel\ReasonStore::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
