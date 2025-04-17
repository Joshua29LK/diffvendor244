<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Resolution\ResourceModel;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Resolution extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_resolution';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ResolutionInterface::RESOLUTION_ID);
    }
}
