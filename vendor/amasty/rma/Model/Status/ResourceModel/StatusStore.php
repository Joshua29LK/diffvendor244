<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Status\ResourceModel;

use Amasty\Rma\Api\Data\StatusStoreInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StatusStore extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_status_store';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, StatusStoreInterface::STATUS_STORE_ID);
    }
}
