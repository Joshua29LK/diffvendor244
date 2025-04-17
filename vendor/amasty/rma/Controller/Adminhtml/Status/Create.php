<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\Status;

use Amasty\Rma\Controller\Adminhtml\AbstractStatus;
use Magento\Framework\Controller\ResultFactory;

class Create extends AbstractStatus
{
    /**
     * @return void
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
