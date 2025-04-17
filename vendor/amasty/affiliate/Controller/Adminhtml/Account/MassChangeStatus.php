<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;

class MassChangeStatus extends \Amasty\Affiliate\Controller\Adminhtml\Account
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

        /** @var \Amasty\Affiliate\Model\Account $item */
        foreach ($collection as $item) {
            $item->setIsAffiliateActive($status);
            $this->accountRepository->save($item);
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
