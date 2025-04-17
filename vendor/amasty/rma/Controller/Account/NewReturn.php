<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Account;

use Amasty\Rma\Api\CreateReturnProcessorInterface;
use Amasty\Rma\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;

class NewReturn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CreateReturnProcessorInterface
     */
    private $createReturnProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Session $customerSession,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        ConfigProvider $configProvider,
        CreateReturnProcessorInterface $createReturnProcessor,
        Context $context
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->createReturnProcessor = $createReturnProcessor;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $orderId = (int)$this->getRequest()->getParam('order');
        if (!$orderId) {
            $this->messageManager->addWarningMessage(__('Order is not set'));

            return $this->resultRedirectFactory->create()->setUrl(
                $this->_url->getUrl($this->configProvider->getUrlPrefix() . '/account/history')
            );
        }

        $order = $this->orderRepository->get((int)$orderId);
        if ($order->getCustomerId() != $customerId) {
            $this->messageManager->addWarningMessage(__('Wrong Order'));

            return $this->resultRedirectFactory->create()->setUrl(
                $this->_url->getUrl($this->configProvider->getUrlPrefix() . '/account/history')
            );
        }

        if (!($returnOrder = $this->createReturnProcessor->process($orderId))) {
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_url->getUrl($this->configProvider->getUrlPrefix() . '/account/history')
            );
        }

        $this->registry->register(
            \Amasty\Rma\Controller\RegistryConstants::CREATE_RETURN_ORDER,
            $returnOrder
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('New Return for Order #%1', $order->getIncrementId()));

        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive($this->configProvider->getUrlPrefix() . '/account/history');
        }

        return $resultPage;
    }
}
