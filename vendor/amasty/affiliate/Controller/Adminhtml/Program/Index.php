<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class Index extends \Amasty\Affiliate\Controller\Adminhtml\Program
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
        $resultPage->addBreadcrumb(__('Affiliate Programs'), __('Affiliate Programs'));
        $resultPage->addBreadcrumb(__('Affiliate Programs'), __('Affiliate Programs'));
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Programs'));

        return $resultPage;
    }
}
