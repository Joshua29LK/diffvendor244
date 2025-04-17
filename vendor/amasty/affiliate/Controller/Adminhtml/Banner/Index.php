<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

class Index extends \Amasty\Affiliate\Controller\Adminhtml\Banner
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
        $resultPage->addBreadcrumb(__('Affiliate Banners'), __('Affiliate Banners'));
        $resultPage->addBreadcrumb(__('Affiliate Banners'), __('Affiliate Banners'));
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Banners'));

        return $resultPage;
    }
}
