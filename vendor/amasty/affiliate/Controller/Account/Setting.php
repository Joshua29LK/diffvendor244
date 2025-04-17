<?php

namespace Amasty\Affiliate\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;

class Setting extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Setting constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->getCustomerId()) {
            return $this->_redirect('customer/account/login');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Settings'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
    }
}
