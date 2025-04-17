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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Block\Adminhtml\Order\Payment as OrderPayment;
use Magento\Sales\Block\Adminhtml\Order\Totals;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Mageplaza\EditOrder\Model\Quote\QuoteManagement;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment\Info as PaymentInfo;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Totals\Tax;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Logs;
use Mageplaza\EditOrder\Model\LogsFactory;
use Mageplaza\EditOrder\Model\Order\Edit as EditModel;
use Mageplaza\EditOrder\Model\Order\Total as OrderTotal;
use Mageplaza\EditOrder\Model\Order\SaveOther;
use Mageplaza\EditOrder\Block\Adminhtml\Logs\Order\PaymentMethod;
use Psr\Log\LoggerInterface;
use Zend\Uri\Uri;

/**
 * Class Save
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit
 */
class Save extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var PaymentResource
     */
    protected $paymentResource;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var OrderTotal
     */
    protected $orderTotal;

    /**
     * @var SaveOther
     */
    protected $saveOther;

    /**
     * @var EditModel
     */
    protected $editModel;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var PaymentMethod
     */
    protected $logsPayment;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Rule
     */
    private $saleRule;

    /**
     * @var bool
     */
    private $_isHaveInvoce = false;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PaymentResource $paymentResource
     * @param PaymentFactory $paymentFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param OrderFactory $orderFactory
     * @param Coupon $coupon
     * @param Rule $saleRule
     * @param LogsFactory $logsFactory
     * @param Session $authSession
     * @param RemoteAddress $remoteAddress
     * @param QuoteFactory $quoteFactory
     * @param InvoiceFactory $invoiceFactory
     * @param HelperData $_helperData
     * @param OrderTotal $orderTotal
     * @param SaveOther $saveOther
     * @param EditModel $editModel
     * @param QuoteSession $quoteSession
     * @param PaymentMethod $paymentMethod
     * @param LoggerInterface $logger
     * @param ItemFactory $orderItemFactory
     * @param QuoteManagement $quoteManagement
     * @param ProductRepositoryInterface $productRepository
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        PaymentResource $paymentResource,
        PaymentFactory $paymentFactory,
        LayoutFactory $resultLayoutFactory,
        OrderFactory $orderFactory,
        Coupon $coupon,
        Rule $saleRule,
        LogsFactory $logsFactory,
        Session $authSession,
        RemoteAddress $remoteAddress,
        QuoteFactory $quoteFactory,
        InvoiceFactory $invoiceFactory,
        HelperData $_helperData,
        OrderTotal $orderTotal,
        SaveOther $saveOther,
        EditModel $editModel,
        QuoteSession $quoteSession,
        PaymentMethod $paymentMethod,
        LoggerInterface $logger,
        ItemFactory $orderItemFactory,
        QuoteManagement $quoteManagement,
        ProductRepositoryInterface $productRepository,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->resultJsonFactory     = $resultJsonFactory;
        $this->paymentResource       = $paymentResource;
        $this->paymentFactory        = $paymentFactory;
        $this->resultLayoutFactory   = $resultLayoutFactory;
        $this->orderFactory          = $orderFactory;
        $this->logsFactory           = $logsFactory;
        $this->authSession           = $authSession;
        $this->remoteAddress         = $remoteAddress;
        $this->quoteFactory          = $quoteFactory;
        $this->invoiceFactory        = $invoiceFactory;
        $this->_helperData           = $_helperData;
        $this->orderTotal            = $orderTotal;
        $this->saveOther             = $saveOther;
        $this->editModel             = $editModel;
        $this->quoteSession          = $quoteSession;
        $this->logsPayment           = $paymentMethod;
        $this->_logger               = $logger;
        $this->coupon                = $coupon;
        $this->saleRule              = $saleRule;
        $this->orderItemFactory      = $orderItemFactory;
        $this->quoteManagement       = $quoteManagement;
        $this->_productRepository    = $productRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $result         = $this->resultJsonFactory->create();
        $orderId        = $this->getRequest()->getParam('order_id');
        $order          = $this->getOrder();
        $newData        = $this->getNewData();
        $oldData        = $this->getOldData();
        $oldPaymentData = $this->getOldPaymentData();
        $oldTotalData   = [
            'subtotal'        => $order->getBaseSubtotal(),
            'shipping_amount' => $order->getBaseShippingAmount(),
            'tax_amount'      => $order->getBaseTaxAmount(),
            'discount_amount' => $order->getBaseDiscountAmount(),
            'grand_total'     => $order->getBaseGrandTotal(),
            'total_paid'      => $order->getBaseTotalPaid(),
            'total_refund'    => $order->getBaseTotalRefunded(),
            'total_due'       => $order->getBaseTotalDue(),
        ];

        $resultData = [];
        $diff       = $this->_helperData->arrayDifferent($newData, $oldData);
        if (!count($diff)) {
            $diff = $this->_helperData->arrayDifferent($oldData, $newData);
        }

        $isEdited = $this->_helperData->isEdited($diff);
        if ($isEdited) {
            /** check if empty item */
            if (isset($oldData['item']) && !isset($newData['item'])) {
                $resultData['items'] = [
                    'error'   => true,
                    'message' => __('There must always be items')
                ];

                return $result->setData($resultData);
            }

            /** save order data */
            if ($orderId && (isset($newData['order']) || isset($newData['item']))) {
                $resultData = $this->setPostData($newData, $diff);
            }

            if (isset($newData['payment'])) {
                $resultData['payment_method'] = $this->editPaymentMethod($order, $newData['payment']);
            }

            /** result error */
            foreach ($resultData as $key => $value) {
                if (isset($resultData[$key]['error'])) {
                    $resultData = [
                        $key => $value
                    ];
                }
            }

            if (!isset($resultData['shipping_method']['error'])
                && !isset($resultData['info']['error'])
                && !isset($resultData['items']['error'])
            ) {
                /** @var Logs $log */
                $log = $this->logsFactory->create();
                $logData = $this->getLogData($oldPaymentData, $oldTotalData, $newData, $oldData, $diff);
                try {
                    $log->addData($logData)->save();
                } catch (Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @param Order $order
     * @param array $newData
     *
     * @return array
     */
    public function editPaymentMethod($order, $newData)
    {
        $paymentId   = 0;
        $paymentData = $newData;

        if ($order->getPayment()) {
            $paymentId = $order->getPayment()->getEntityId();
        }
        /** @var Payment $payment */
        $payment = $this->paymentFactory->create();
        $this->paymentResource->load($payment, $paymentId);
        $order->setPayment($payment);
        $payment->addData($paymentData);

        try {
            $payment->save();
            $this->messageManager->addSuccessMessage(__('This order has been updated!'));
            $resultData = ['success' => $this->getPaymentHtml($order)];
        } catch (Exception $e) {
            $resultData['payment_save_error'] = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $resultData;
    }

    /**
     * @param array $data
     * @param array $diffData
     *
     * @return array
     * @throws LocalizedException
     */
    public function setPostData($data, $diffData)
    {
        $result     = [];
        $order      = $this->getOrder();

        if ($order->getMpOldOrderData() === null) {
            $order->setMpOldOrderData(json_encode($order->getData()));
            $order->save();
        }

        if (isset($diffData['item'])) {
            $result['changeProduct'] = 1;
        } else {
            $result['changeProduct'] = 0;
        }

        if (isset($data['item'])) {
            $this->applyCoupon($data, $order);
            $result['items'] = $this->editItems($order);
        }

        if (isset($data['order'])) {
            $orderData = $data['order'];

            if (!is_array($diffData)) {
                return $result;
            }

            if (isset($diffData['order']['billing_address'])) {
                $result['billing_address'] = $this->editModel->setAddress(
                    $order->getId(),
                    $orderData['billing_address']
                );
            }

            if (isset($diffData['order']['shipping_address'])) {
                $result['shipping_address'] = $this->editModel->setAddress(
                    $order->getId(),
                    $orderData['shipping_address']
                );
            }

            if (isset($diffData['order']['info'])) {
                $result['info'] = $this->editModel->setInfoData($order, $diffData['order']['info']);
            }

            if (isset($diffData['order']['customer'])) {
                $result['customer'] = $this->editModel->setCustomerData($order, $orderData['customer']);
            }

            if (isset($diffData['order']['shipping_method']) || isset($diffData['method_detail'])) {
                $shipMethod = $orderData['shipping_method'];

                if ($shipMethod === 'freeshipping_freeshipping') {
                    $shipData = [
                        'ship_amount'          => 0,
                        'ship_tax_percent'     => 0,
                        'ship_discount_amount' => 0,
                        'total_fee'            => 0,
                        'ship_description'     => __('Free Shipping'),
                        'method'               => 'freeshipping_freeshipping',
                        'type'                 => OrderTotal::TYPE_COLLECT_SHIPPING
                    ];
                } else {
                    $shipData           = $data['method_detail'][$shipMethod];
                    $shipData['method'] = $shipMethod;
                    $shipData['type']   = OrderTotal::TYPE_COLLECT_SHIPPING;
                }

                $result['shipping_method'] = $this->setShippingMethod($shipData);
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param Order $order
     *
     * @return array
     * @throws Exception
     */
    public function applyCoupon($data, $order)
    {
        if (isset($data['mp_coupon_code'])) {
            $discountRule = $this->getDiscountRule($data['mp_coupon_code']);
            if ($order->getCouponCode() != $data['mp_coupon_code']) {
                $this->plusTimeUsedCouponCode($data['mp_coupon_code']);
                if ($order->getCouponCode()) {
                    $this->minusTimeUsedCouponCode($order->getCouponCode());
                }
            }
            $order->setCouponCode($data['mp_coupon_code']);
            $order->setData('coupon_rule_name', $discountRule->getName());
            $order->setData(OrderInterface::DISCOUNT_DESCRIPTION, $data['mp_coupon_code']);
        } else {
            if ($order->getCouponCode()) {
                $this->minusTimeUsedCouponCode($order->getCouponCode());
            }
            $order->setCouponCode(null);
            $order->setData(OrderInterface::DISCOUNT_DESCRIPTION, null);
        }

        try {
            $order->save();
            $result = [
                'success' => true
            ];
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * @param $couponCode
     *
     * @throws Exception
     */
    protected function plusTimeUsedCouponCode($couponCode)
    {
        $this->coupon->loadByCode($couponCode);
        $this->coupon->setTimesUsed($this->coupon->getTimesUsed() + 1);
        $this->coupon->save();
    }

    /**
     * @param $couponCode
     *
     * @throws Exception
     */
    protected function minusTimeUsedCouponCode($couponCode)
    {
        $this->coupon->loadByCode($couponCode);
        $this->coupon->setTimesUsed($this->coupon->getTimesUsed() - 1);
        $this->coupon->save();
    }

    /**
     * @param $couponCode
     *
     * @return Rule
     */
    public function getDiscountRule($couponCode)
    {
        $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();

        return $this->saleRule->load($ruleId);
    }

    /**
     * @return array
     */
    public function getOldData()
    {
        $uri = new Uri();

        return $uri->setQuery($this->getRequest()->getParam('oldData'))->getQueryAsArray();
    }

    /**
     * @return array
     */
    public function getNewData()
    {
        $uri = new Uri();

        return $uri->setQuery($this->getRequest()->getParam('newData'))->getQueryAsArray();
    }

    /**
     * @return mixed|string
     */
    public function getOldPaymentData()
    {
        return $this->getRequest()->getParam('paymentMethod') ? $this->getRequest()->getParam('paymentMethod') : '';
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function editItems($order)
    {
        $quote       = $this->quoteFactory->create()->load($this->quoteSession->getQuoteId());
        $newData     = $this->getNewData();
        $oldData     = $this->getOldData();
        $diff        = $this->_helperData->arrayDifferent($newData, $oldData);
        $qty         = [];
        $diffData    = [];

        if (isset($diff['item'])) {
            foreach ($diff['item'] as $item) {
                $diffData[] = $item;
            }
        }

        foreach ($oldData['item'] as $itemValue) {
            if (isset($itemValue['qty'])) {
                $qty[$itemValue['sku']] = (float) $itemValue['qty'];
            }
        }

        try {
            $itemData          = $this->editModel->getQuoteItemsData($quote);
            $itemData['items'] = $newData['item'];
            $orderData         = [];

            foreach ($order->getAllItems() as $item) {
                $orderData['invoiceData'][$item->getProductId()]    = $item->getQtyInvoiced();
                $orderData['shipmentData'][$item->getProductId()]    = $item->getQtyShipped();
                $orderData['creditmemoData'][$item->getProductId()] = $item->getQtyRefunded();
                $orderData['oldItems'] []                           = $item->getProductId();
            }

            $itemData['orderData'] = $orderData;
            $itemData['diffData'] = $diffData;

            $status      = $this->orderTotal->saveOrder($order, $itemData, $qty);

            $invoices    = $this->getInvoices($order);
            $shipments   = $this->getShipments($order);
            $creditmemos = $this->getCreditMemos($order);

            if (isset($status['success'])) {
                $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];

                if ($updateAfterEditingConfig === 1) {
                    $this->saveOther->creatAndReset($order, $orderData, $diffData);
                } elseif ($updateAfterEditingConfig === 0) {
                    $this->saveOther->updateOrder($order, $invoices, $shipments, $creditmemos, $orderData, $diffData);
                }

                $quote = $this->quoteFactory->create()->load($this->quoteSession->getQuoteId());
                if (!empty($this->quoteManagement->getResolveItems($quote))) {
                    $this->bundleItems($order, $quote);
                }

                $result = [
                    'success' => [
                        'itemsHtml'      => $this->getItemsHtml($this->getOrder()),
                        'orderTotalHtml' => $this->getOrderTotalHtml($this->getOrder()),
                    ]
                ];
            } else {
                $result = [
                    'error'   => true,
                    'message' => $status['error']
                ];
            }
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * @param $order
     * @param $quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function bundleItems($order, $quote)
    {
        $existItems            = [];
        $existItemSku          = [];
        $existItemCaB          = [];
        $parentItemCaB         = [];
        $parentKeyCaB          = [];
        $quoteItemIds          = [];
        $childItemIds          = [];
        $childParentIds        = [];

        foreach ($this->quoteManagement->getResolveItems($quote) as $quoteItem) {
            $product = $this->_productRepository->getById($quoteItem->getProductId());
            $quoteItemIds[$quoteItem->getSku()] = $quoteItem->getId();
            if ($product->getTypeId() === 'configurable' || $product->getTypeId() === 'bundle') {
                if ($product->getTypeId() === 'configurable') {
                    $childrens = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($childrens as $child) {
                        $childItemIds[$child->getID()]   = $quoteItem->getProductId();
                        $childParentIds[$child->getID()] = $product->getId();
                    }
                }

                if ($product->getTypeId() === 'bundle') {
                    $requiredChildrenIds = $product->getTypeInstance()->getChildrenIds($product->getId(), false);
                    foreach ($requiredChildrenIds as $requiredChildrenId) {
                        foreach ($requiredChildrenId as $productId) {
                            $childItemIds[$productId]   = $quoteItem->getProductId();
                            $childParentIds[$productId] = $product->getId();
                        }
                    }
                }

                $existItemCaB[]  = $quoteItem->getProductId();
            }
            if (isset($orderItems[$quoteItem->getSku()])) {
                $existItems[$quoteItem->getSku()] = $quoteItem;
                $existItemSku[] = $quoteItem->getSku();
            }
        }

        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getProductId(), $existItemCaB)) {
                $parentItemCaB[$item->getProductId()] = $item->getItemId();
                $parentKeyCaB [] = $item->getProductId();
            }
        }

        $this->setParentIdItems($order, $childParentIds, $existItemCaB, $parentItemCaB);


        $itemCollection    = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $order->getId());

        $itemCollectionIds = $this->getItemCollectionIds($itemCollection);
        foreach ($itemCollection as $itemData) {
            if ($itemData->getParentItemId()) {
                if (!in_array($itemData->getParentItemId(), $itemCollectionIds)) {
                    $itemData->delete();
                }
            }
        }
    }

    /**
     * @param $order
     * @param $childParentIds
     * @param $existItemCaB
     * @throws Exception
     */
    public function setParentIdItems($order, $childParentIds, $existItemCaB, $parentItemCaB)
    {
        $orderItemFactory = $this->orderItemFactory->create();
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getProductType() !== 'configurable' && $orderItem->getProductType() !== 'bundle') {
                if ($orderItem->getParentItemId() === null) {
                    if (isset($childItemIds[$orderItem->getProductId()])) {
                        $parentId = $childParentIds[$orderItem->getProductId()];
                        if (in_array($childItemIds[$orderItem->getProductId()], $existItemCaB) && isset($parentItemCaB[$parentId])) {
                            $parentItemId = $parentItemCaB[$parentId];
                            $orderItemFactory->load($orderItem->getId())->setParentItemId($parentItemId)->save();
                        }
                    }
                } elseif (!in_array($orderItem->getParentItemId(), $parentItemCaB)) {
                    $orderItemFactory->load($orderItem->getId())->delete();
                }
            }
        }
    }

    /**
     * @param $itemCollection
     * @return array
     */
    public function getItemCollectionIds($itemCollection)
    {
        $itemCollectionIds = [];
        foreach ($itemCollection as $value) {
            $itemCollectionIds[] = $value->getItemId();
        }

        return $itemCollectionIds;
    }

    /**
     * @param $order
     * @return array
     */
    public function getInvoices($order)
    {
        $invoices = [];
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoices[] = $invoice;
            if ((float) $invoice->getState() != 3) {
                $this->_isHaveInvoce = true;
            }
        }

        return $invoices;
    }

    /**
     * @param $order
     * @return array
     */
    public function getShipments($order)
    {
        $shipments = [];
        foreach ($order->getShipmentsCollection() as $ship) {
            $shipments[] = $ship;
        }

        return $shipments;
    }

    /**
     * @param $order
     * @return array
     */
    public function getCreditMemos($order)
    {
        $creditmemos = [];
        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $creditmemos[] = $creditmemo;
        }

        return $creditmemos;
    }

    /**
     * Get html items order
     *
     * @param Order $order
     *
     * @return string
     */
    public function getItemsHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addHandle('mpeditorder_items_view');
        $resultLayout->getLayout()->getBlock('order_tab_info')->setCurrentOrder($order);

        return $resultLayout->getLayout()->getBlock('order_items')->toHtml();
    }

    /**
     * @param array $newData
     * @param array $oldData
     * @param array $diff
     *
     * @return mixed
     */
    public function getLogData($oldPaymentData, $oldTotalData, $newData, $oldData, $diff)
    {
        $totalData = [];

        /** @var Order $order */
        $orderId = $this->getRequest()->getParam('order_id');
        $order   = $this->orderFactory->create()->load($orderId);

        if (isset($diff['method_detail']) || isset($diff['order']['shipping_method']) || isset($diff['item'])) {
            $totalData = $oldTotalData;
        }

        if (isset($oldData['payment']) && isset($newData['payment'])) {
            if ($oldPaymentData) {
                $oldData['payment_content'] = $oldPaymentData;
            }

            $newPayment = $this->logsPayment->getPaymentHtml($order->getPayment());
            if ($newPayment) {
                $newData['payment_content'] = $newPayment;
            }
        }

        $data['order_id']       = $order->getId();
        $data['editor']         = $this->getAdminUserName();
        $data['editor_id']      = $this->getAdminUserId();
        $data['editor_ip']      = $this->remoteAddress->getRemoteAddress();
        $data['order_number']   = $order->getIncrementId();
        $data['edited_type']    = $this->_helperData->getEditedType($diff);
        $data['old_data']       = HelperData::jsonEncode($oldData);
        $data['new_data']       = HelperData::jsonEncode($newData);
        $data['old_total_data'] = HelperData::jsonEncode($totalData);
        $data['created_at']     = date(DateTime::DATETIME_PHP_FORMAT);

        return $data;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * @param array $shipData
     *
     * @return array
     */
    public function setShippingMethod($shipData)
    {
        try {
            $oldShippingTotal = $this->getOrder()->getShippingAmount();
            $order  = $this->getOrder();
            $status = $this->orderTotal->saveOrder($this->getOrder(), $shipData);

            if (isset($status['success'])) {
                $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];

                if ($updateAfterEditingConfig === 1) {
                    //Change invoice and creditmemo
                    $issetInvoice   = false;
                    $invoiceFactory = $this->invoiceFactory->create();

                    foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
                        if ((int)$invoice->getState() !== 3) {
                            $issetInvoice  = true;
                            $invoiceFactory->load($invoice->getId());
                            $invoiceFactory->setBaseShippingAmount($shipData['ship_amount']);
                            $invoiceFactory->setBaseShippingTaxAmount((float)$shipData['ship_discount_amount'] * (float)$shipData['ship_amount']);
                            $invoiceFactory->setBaseShippingDiscountAmount($shipData['ship_discount_amount']);
                            $invoiceFactory->setBaseShippingInclTax($shipData['total_fee']);
                            $invoiceFactory->setShippingAmount($shipData['ship_amount']);
                            $invoiceFactory->setShippingTaxAmount((float)$shipData['ship_discount_amount'] * (float)$shipData['ship_amount']);
                            $invoiceFactory->setShippingDiscountAmount($shipData['ship_discount_amount']);
                            $invoiceFactory->setBaseGrandTotal((float)$invoice->getBaseGrandTotal() + (float)$shipData['total_fee']);
                            $invoiceFactory->setGrandTotal((float)$invoice->getGrandTotal() - $oldShippingTotal + (float)$shipData['total_fee']);

                            $invoiceFactory->addComment(__('Invoice has been changed by Mageplaza EditOrder.'));
                            $invoiceFactory->save();
                        }
                    }

                    if ($issetInvoice) {
                        $order = $this->getOrder();
                        $order->setTotalPaid($order->getGrandTotal());
                        $order->setBaseTotalPaid($order->getBaseGrandTotal());
                        $order->save();
                    }

                    //Update creditmemo
                    $creditShippingTotal  = 0;
                    $creditDiffCheck      = false;

                    foreach ($order->getCreditmemosCollection() as $creditmemo) {
                        if ((int) $creditmemo->getState() !== 3) {
                            $diffValue = (float) $shipData['ship_amount'] - (float) $creditmemo->getShippingAmount();
                            $creditmemo->setBaseShippingAmount((float) $creditmemo->getShippingAmount() + $diffValue);
                            $creditmemo->setShippingAmount((float) $creditmemo->getShippingAmount() + $diffValue);
                            $creditmemo->setBaseGrandTotal((float) $creditmemo->getBaseGrandTotal() - $oldShippingTotal);
                            $creditmemo->setGrandTotal((float) $creditmemo->getGrandTotal() + $diffValue);
                            $creditmemo->addComment(__('Creditmemo has been changed by Mageplaza EditOrder.'));
                            $creditmemo->save();
                            $creditShippingTotal += $creditmemo->getShippingAmount();
                            $creditDiffCheck = true;
                        }
                    }

                    if ($creditDiffCheck) {
                        $order->setBaseShippingRefunded($creditShippingTotal);
                        $order->setShippingRefunded($creditShippingTotal);
                        $order->save();
                    }
                } else {
                    //Update order
                    $issetInvoice        = false;
                    $creditDiffCheck     = false;
                    $creditShippingTotal = 0;

                    foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
                        if ((int)$invoice->getState() !== 3) {
                            $issetInvoice  = true;
                        }
                    }

                    if ($issetInvoice) {
                        $order = $this->getOrder();
                        $order->setTotalPaid($order->getGrandTotal());
                        $order->setBaseTotalPaid($order->getBaseGrandTotal());
                        $order->save();
                    }

                    foreach ($order->getCreditmemosCollection() as $creditmemo) {
                        if ((int) $creditmemo->getState() !== 3) {
                            $diffValue            = (float) $shipData['ship_amount'] - (float) $creditmemo->getShippingAmount();
                            $creditShippingTotal += (float) $creditmemo->getShippingAmount() + $diffValue;
                            $creditDiffCheck      = true;
                        }
                    }

                    if ($creditDiffCheck) {
                        $order->setBaseShippingRefunded($creditShippingTotal);
                        $order->setShippingRefunded($creditShippingTotal);
                        $order->save();
                    }
                }

                $result = [
                    'success' => [
                        'orderTotalHtml' => $this->getOrderTotalHtml($this->getOrder()),
                        'shippingMethodHtml' => $this->getShippingMethodHtml($this->getOrder())
                    ]
                ];
            }
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => __('Unknown error, Unable to update order.')
            ];
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @return mixed
     */
    public function getPaymentHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $child        = $resultLayout->getLayout()->createBlock(OrderPayment::class);

        return $resultLayout->getLayout()->createBlock(PaymentInfo::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/payment/method/info.phtml')
            ->setCurrentOrder($order)
            ->setChild('order_payment', $child)
            ->toHtml();
    }

    /**
     * Get order total html
     *
     * @param Order $order
     *
     * @return mixed
     */
    public function getOrderTotalHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        /** set tax row */
        $tax = $resultLayout->getLayout()->createBlock(Tax::class)
            ->setCurrentOrder($order)
            ->setTemplate('Magento_Sales::order/totals/tax.phtml');

        return $resultLayout->getLayout()->createBlock(Totals::class)
            ->setOrder($order)
            ->setTemplate('Magento_Sales::order/totals.phtml')
            ->setChild('tax', $tax)
            ->toHtml();
    }

    /**
     * Get shipping method html
     *
     * @param Order $order
     *
     * @return string
     */
    public function getShippingMethodHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout->getLayout()
            ->createBlock(AbstractOrder::class)->setOrder($order)
            ->setTemplate('Magento_Shipping::order/view/info.phtml')
            ->toHtml();
    }

    /**
     * @return int
     */
    public function getAdminUserId()
    {
        $user = $this->authSession->getUser();

        if ($user) {
            return $user->getId();
        }

        return 0;
    }

    /**
     * @return mixed|string
     */
    public function getAdminUserName()
    {
        $user = $this->authSession->getUser();

        if ($user) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        }

        return '';
    }
}
