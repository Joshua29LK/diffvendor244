<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

class Delete extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->bannerRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The banner has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
