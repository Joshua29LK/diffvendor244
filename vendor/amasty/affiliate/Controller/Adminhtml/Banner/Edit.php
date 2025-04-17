<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

use Amasty\Affiliate\Model\RegistryConstants;

class Edit extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->bannerRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                $this->_redirect('amasty_affiliate/*');
                return;
            }
        } else {
            /** @var \Amasty\Affiliate\Model\Banner $model */
            $model = $this->bannerFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_AFFILIATE_BANNER, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Banner'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getBannerId() ? $model->getTitle() : __('New Banner')
        );

        $breadcrumb = $id ? __('Manage Banner') : __('New Banner');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
