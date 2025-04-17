<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\ReturnRules\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ReturnRulesResolutionsCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Rma\Model\ReturnRules\ReturnRulesResolutions::class,
            \Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRulesResolutions::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
