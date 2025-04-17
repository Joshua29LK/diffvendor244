<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\Resolution;

use Amasty\Rma\Controller\Adminhtml\AbstractResolution;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractResolution
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Rma::resolution');
        $resultPage->addBreadcrumb(__('RMA'), __('RMA'));
        $resultPage->addBreadcrumb(__('Return Resolutions'), __('Return Resolutions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Return Resolutions'));

        return $resultPage;
    }
}
