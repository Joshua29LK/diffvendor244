<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
