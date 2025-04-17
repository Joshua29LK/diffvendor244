<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Reason\ResourceModel;

use Amasty\Rma\Api\Data\ReasonInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Reason extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_reason';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReasonInterface::REASON_ID);
    }
}
