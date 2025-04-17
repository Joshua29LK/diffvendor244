<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Withdrawal;

use Amasty\Affiliate\Model\RegistryConstants;

class Edit extends \Amasty\Affiliate\Controller\Adminhtml\Withdrawal
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->withdrawalRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This withdrawal no longer exists.'));
                $this->_redirect('amasty_affiliate/*');
                return;
            }
        } else {
            /** @var \Amasty\Affiliate\Model\Withdrawal $model */
            $model = $this->withdrawalFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AFFILIATE_WITHDRAWAL, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Affiliate Withdrawal'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('New Affiliate Withdrawal')
        );

        $breadcrumb = $id ? __('Manage Affiliate Withdrawal') : __('Manage Affiliate Withdrawal');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
