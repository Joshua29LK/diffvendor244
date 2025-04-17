<?php

namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

class Save extends \Amasty\ShopbyBase\Controller\Adminhtml\Option\Save
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyBrand::slider');
    }

    protected function _redirectRefer()
    {
        //phpcs:ignore Magento2.Legacy.ObsoleteResponse.ForwardResponseMethodFound
        $this->_forward('index');
    }
}
