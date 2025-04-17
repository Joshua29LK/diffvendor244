<?php

namespace Amasty\Affiliate\Controller\Account\Withdrawal;

class Repeat extends \Amasty\Affiliate\Controller\Account\Withdrawal\AbstractWithdrawal
{
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('withdrawal_id');
        /** @var \Amasty\Affiliate\Model\Withdrawal $withdrawal */
        $withdrawal = $this->withdrawalRepository->get($id);

        if (!$this->validateWithdrawal(-$withdrawal->getCommission())) {
            return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
        }

        $withdrawal->repeat();

        $this->messageManager->addSuccessMessage(__('Withdrawal was successfully created.'));

        return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
    }
}
