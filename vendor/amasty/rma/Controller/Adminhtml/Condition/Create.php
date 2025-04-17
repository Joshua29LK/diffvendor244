<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\Condition;

use Amasty\Rma\Controller\Adminhtml\AbstractCondition;
use Magento\Framework\Controller\ResultFactory;

class Create extends AbstractCondition
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
