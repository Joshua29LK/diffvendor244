<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Transaction;

class Index extends \Amasty\Affiliate\Controller\Adminhtml\Transaction
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
        $resultPage->addBreadcrumb(__('Affiliate Transactions'), __('Affiliate Transactions'));
        $resultPage->addBreadcrumb(__('Affiliate Transactions'), __('Affiliate Transactions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Transactions'));

        return $resultPage;
    }
}
