<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

use Magento\Framework\Controller\ResultFactory;

class MassChangeStatus extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    /**
     * Change Status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $status = $this->getRequest()->getParam('status');

        foreach ($collection as $item) {
            $item->setStatus($status);
            $item->save();
        }

        $message = 'A total of %1 record(s) have been ';
        if ($status == true) {
            $message = $message . 'enabled.';
        } else {
            $message = $message . 'disabled.';
        }

        $this->messageManager->addSuccessMessage(__($message, $collection->getSize()));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
