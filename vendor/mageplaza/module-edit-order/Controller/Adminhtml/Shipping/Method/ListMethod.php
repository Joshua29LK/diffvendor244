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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Shipping\Method;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\ShippingMethod;
use Mageplaza\EditOrder\Model\Order\Create as EditOderCreate;

/**
 * Class ListMethod
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Shipping\Method
 */
class ListMethod extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var EditOderCreate
     */
    protected $editOderCreate;

    /**
     * ListMethod constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param QuoteSession $quoteSession
     * @param OrderFactory $orderFactory
     * @param EditOderCreate $editOderCreate
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        QuoteSession $quoteSession,
        OrderFactory $orderFactory,
        EditOderCreate $editOderCreate
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->quoteSession        = $quoteSession;
        $this->orderFactory        = $orderFactory;
        $this->editOderCreate      = $editOderCreate;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $result       = $this->resultJsonFactory->create();
        $orderId      = $this->getRequest()->getParam('order_id');
        $order        = $this->orderFactory->create()->load($orderId);

        $this->quoteSession->clearStorage();
        $this->quoteSession->setUseOldShippingMethod(true);
        $this->editOderCreate->initFromOrder($order);

        $listMethodHtml = $resultLayout->getLayout()
            ->createBlock(ShippingMethod::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/shipping/method/list.phtml')
            ->setOrderId($orderId)
            ->toHtml();

        $result->setData(['success' => $listMethodHtml]);

        return $result;
    }
}
