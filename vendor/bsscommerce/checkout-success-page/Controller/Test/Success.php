<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Controller\Test;

/**
 * Class Success
 * @package Bss\CheckoutSuccessPage\Controller\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Order
     */
    protected $helperOrder;

    /**
     * Success constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\CheckoutSuccessPage\Helper\Order $helperOrder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\CheckoutSuccessPage\Helper\Order $helperOrder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helperOrder = $helperOrder;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $order = $this->helperOrder->getOrderController();
        $this->helperOrder->registerOrder($order);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('checkoutsuccess_index_index');
        return $resultPage;
    }
}
