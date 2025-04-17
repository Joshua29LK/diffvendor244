<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class Delete extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->programRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The program has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find a page to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
