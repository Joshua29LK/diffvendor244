<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\ReturnRules\ResourceModel;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Rma\Model\ReturnRules\ReturnRules::class,
            \Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRules::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
