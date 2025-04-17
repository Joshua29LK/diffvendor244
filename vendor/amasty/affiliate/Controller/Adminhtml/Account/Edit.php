<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

use Amasty\Affiliate\Model\RegistryConstants;

class Edit extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->accountRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This account no longer exists.'));
                $this->_redirect('amasty_affiliate/*');
                return;
            }
        } else {
            /** @var \Amasty\Affiliate\Model\Account $model */
            $model = $this->accountFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Affiliate Account'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getAccountId() ? $model->getFirstname() . ' ' . $model->getLastname() : __('New Affiliate Account')
        );

        $breadcrumb = $id ? __('Manage Affiliate Account') : __('New Affiliate Account');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
