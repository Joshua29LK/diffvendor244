<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\Reason;

use Amasty\Rma\Controller\Adminhtml\AbstractReason;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractReason
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Rma::reason');
        $resultPage->addBreadcrumb(__('RMA'), __('RMA'));
        $resultPage->addBreadcrumb(__('Return Reasons'), __('Return Reasons'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Return Reasons'));

        return $resultPage;
    }
}
