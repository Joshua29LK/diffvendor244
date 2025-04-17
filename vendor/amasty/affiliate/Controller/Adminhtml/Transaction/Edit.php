<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Transaction;

use Amasty\Affiliate\Model\RegistryConstants;

class Edit extends \Amasty\Affiliate\Controller\Adminhtml\Transaction
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->transactionRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This transaction no longer exists.'));
                $this->_redirect('amasty_affiliate/*');
                return;
            }
        } else {
            /** @var \Amasty\Affiliate\Model\Transaction $model */
            $model = $this->transactionFactory->create();
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Affiliate Transaction'));

        $breadcrumb = __('Manage Affiliate Transaction');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
