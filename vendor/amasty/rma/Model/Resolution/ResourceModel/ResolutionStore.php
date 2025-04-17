<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Resolution\ResourceModel;

use Amasty\Rma\Api\Data\ResolutionStoreInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ResolutionStore extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_resolution_store';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ResolutionStoreInterface::RESOLUTION_STORE_ID);
    }
}
