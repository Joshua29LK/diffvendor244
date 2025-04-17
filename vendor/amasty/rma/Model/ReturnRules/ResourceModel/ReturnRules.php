<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\ReturnRules\ResourceModel;

use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Magento\Rule\Model\ResourceModel\AbstractResource;

class ReturnRules extends AbstractResource
{
    public const TABLE_NAME = 'amasty_rma_return_rules';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReturnRulesInterface::ID);
    }
}
