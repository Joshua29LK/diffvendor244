<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
