<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Chat\ResourceModel;

use Amasty\Rma\Api\Data\MessageFileInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MessageFile extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_message_file';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, MessageFileInterface::MESSAGE_FILE_ID);
    }
}
