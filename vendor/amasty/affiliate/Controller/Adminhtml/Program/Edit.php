<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

use Amasty\Affiliate\Model\RegistryConstants;

class Edit extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->programRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This program no longer exists.'));
                $this->_redirect('amasty_affiliate/*');
                return;
            }
        } else {
            /** @var \Amasty\Affiliate\Model\Program $model */
            $model = $this->programFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AFFILIATE_PROGRAM, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Affiliate Program'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getProgramId() ? $model->getName() : __('New Affiliate Program')
        );

        $breadcrumb = $id ? __('Manage Affiliate Program') : __('New Affiliate Program');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
