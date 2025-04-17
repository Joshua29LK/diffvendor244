<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Withdrawal;

class Cancel extends \Amasty\Affiliate\Controller\Adminhtml\Withdrawal
{
    public function execute()
    {
        try {
            /** @var \Amasty\Affiliate\Model\Withdrawal $model */
            $model = $this->withdrawalFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model = $this->withdrawalRepository->get($id);
            }
            $model->setStatus($model::STATUS_CANCELED);

            $this->withdrawalRepository->save($model);
            $this->messageManager->addSuccessMessage(__('The withdrawal was canceled.'));
            $model->sendEmail(\Amasty\Affiliate\Model\Mailsender::TYPE_AFFILIATE_WITHDRAWAL_STATUS);

            $this->_redirect('amasty_affiliate/withdrawal/edit', ['id' => $model->getTransactionId()]);
            return;
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $id = (int)$this->getRequest()->getParam('id');
            if (!empty($id)) {
                $this->_redirect('amasty_affiliate/withdrawal/edit', ['id' => $id]);
            } else {
                $this->_redirect('amasty_affiliate/withdrawal/new');
            }
            return;
        }
        $this->_redirect('amasty_affiliate/withdrawal/index');
    }
}
