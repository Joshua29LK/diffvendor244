<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Withdrawal;

class Index extends \Amasty\Affiliate\Controller\Adminhtml\Withdrawal
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
        $resultPage->addBreadcrumb(__('Affiliate Withdrawals'), __('Affiliate Withdrawals'));
        $resultPage->addBreadcrumb(__('Affiliate Withdrawals'), __('Affiliate Withdrawals'));
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Withdrawals'));

        return $resultPage;
    }
}
