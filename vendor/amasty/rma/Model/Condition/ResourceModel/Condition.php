<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Condition\ResourceModel;

use Amasty\Rma\Api\Data\ConditionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Condition extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_item_condition';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ConditionInterface::CONDITION_ID);
    }
}
