<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\History\ResourceModel;

use Amasty\Rma\Api\Data\HistoryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_history';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, HistoryInterface::EVENT_ID);
    }
}
