<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Reason\ResourceModel;

use Amasty\Rma\Api\Data\ReasonStoreInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReasonStore extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_reason_store';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReasonStoreInterface::REASON_STORE_ID);
    }
}
