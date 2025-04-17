<?php

namespace Amasty\Affiliate\Controller\Account;

class Promo extends \Amasty\Affiliate\Controller\Account\AbstractTab
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->authenticate()) {
            return $this->_redirect('*/account/setting');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Promo Data'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
    }
}
