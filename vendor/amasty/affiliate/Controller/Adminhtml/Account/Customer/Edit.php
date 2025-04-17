<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account\Customer;

class Edit extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
