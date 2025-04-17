<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\ReturnRules\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ReturnRulesWebsitesCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Rma\Model\ReturnRules\ReturnRulesWebsites::class,
            \Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRulesWebsites::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
