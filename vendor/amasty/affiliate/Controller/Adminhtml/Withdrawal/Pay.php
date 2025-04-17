<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Withdrawal;

class Pay extends \Amasty\Affiliate\Controller\Adminhtml\Withdrawal
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

            if ($model->getStatus() != $model::STATUS_PENDING) {
                $this->messageManager->addErrorMessage(__('The withdrawal was already processed'));
                return $this->_redirect('amasty_affiliate/withdrawal/edit', ['id' => $model->getTransactionId()]);
            }

            /** @var \Amasty\Affiliate\Model\Account $account */
            $account = $this->accountRepository->get($model->getAffiliateAccountId());

            if ($account->getBalance() < $model->getCommission()) {
                $this->messageManager->addErrorMessage(__('The account has not enough funds to this withdrawal'));
                return $this->_redirect('amasty_affiliate/withdrawal/edit', ['id' => $model->getTransactionId()]);
            }

            $commission = -$model->getCommission();
            $account->setBalance($account->getBalance() - $commission);
            $account->setCommissionPaid($account->getCommissionPaid() + $commission);
            $this->accountRepository->save($account);

            $model->setBalance($account->getBalance());
            $model->setStatus($model::STATUS_COMPLETED);
            $this->withdrawalRepository->save($model);

            $this->messageManager->addSuccessMessage(__('The withdrawal was payed.'));
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
