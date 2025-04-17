<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Controller\Adminhtml\Payment\Method;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment\ListPayment;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\PaymentMethod;
use Mageplaza\EditOrder\Model\Order\Create as EditOderCreate;

/**
 * Class ListMethod
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Payment\Method
 */
class ListMethod extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var EditOderCreate
     */
    protected $editOderCreate;

    /**
     * ListMethod constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param EditOderCreate $editOderCreate
     * @param QuoteSession $quoteSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        EditOderCreate $editOderCreate,
        QuoteSession $quoteSession
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->orderFactory        = $orderFactory;
        $this->orderRepository     = $orderRepository;
        $this->quoteSession        = $quoteSession;
        $this->editOderCreate      = $editOderCreate;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $result       = $this->resultJsonFactory->create();
        $resultLayout = $this->resultLayoutFactory->create();
        $this->quoteSession->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order   = $this->orderFactory->create()->load($orderId);

        $this->editOderCreate->initFromOrder($order);

        if ($order->getPayment()) {
            $paymentMethodCode = $order->getPayment()->getMethod();

            $child = $resultLayout->getLayout()
                ->createBlock(ListPayment::class, 'edit_order_payment_form')
                ->setTemplate('Magento_Sales::order/create/billing/method/form.phtml')
                ->setPaymentCode($paymentMethodCode)->setPoNumber($order->getPayment()->getPoNumber());

            $listMethodHtml = $resultLayout->getLayout()
                ->createBlock(PaymentMethod::class)
                ->setTemplate('Mageplaza_EditOrder::order/edit/payment/method/list.phtml')
                ->setChild('billing_method', $child)
                ->toHtml();

            $result->setData(['success' => $listMethodHtml]);
        }

        return $result;
    }
}
