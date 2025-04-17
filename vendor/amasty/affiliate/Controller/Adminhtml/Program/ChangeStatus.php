<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class ChangeStatus extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    /**
     * Change Status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                /** @var \Amasty\Affiliate\Model\Program $model */
                $model = $this->programRepository->get($id);
                $model->setIsActive($status);
                $this->programRepository->save($model);
                $this->messageManager->addSuccess(__('Status for Program with ID %1 has been changed.', $id));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
