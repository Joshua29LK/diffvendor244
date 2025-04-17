<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

class Index extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Affiliate::affiliate');
        $resultPage->addBreadcrumb(__('Affiliate Accounts'), __('Affiliate Accounts'));
        $resultPage->addBreadcrumb(__('Affiliate Accounts'), __('Affiliate Accounts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Accounts'));

        return $resultPage;
    }
}
