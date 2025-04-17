<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
