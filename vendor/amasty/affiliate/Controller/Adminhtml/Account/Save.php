<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

use Amasty\Affiliate\Controller\Adminhtml\Account;

class Save extends Account
{
    /**
     * @var string[]
     */
    private $saveNumericArray = ['balance', 'lifetime_commission', 'commission_paid', 'on_hold'];

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                if (!isset($data['account_id'])) {
                    return;
                }
                /** @var \Amasty\Affiliate\Model\Account $model */
                $model = $this->accountFactory->create();
                $store = $this->storeManager->getStore($this->getRequest()->getParam('store_id'));
                $currency = $this->currency->getCurrency($store->getBaseCurrencyCode());

                /**
                 * TODO: Refactor UI form for fields balance and another with currencies.
                 */
                foreach ($this->saveNumericArray as $element) {
                    $data[$element] = str_replace($currency->getSymbol(), '', $data[$element]);
                }

                $model->setData($data);
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model = $this->accountRepository->get($id);
                }

                $this->accountRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The account is saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_affiliate/account/edit', ['id' => $model->getAccountId()]);

                    return;
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_affiliate/account/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_affiliate/account/new');
                }

                return;
            }

        }
        $this->_redirect('amasty_affiliate/account/index');
    }
}
