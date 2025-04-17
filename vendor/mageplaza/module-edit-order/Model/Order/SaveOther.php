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

namespace Mageplaza\EditOrder\Model\Order;

use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Order\InvoiceService as OrderInvoiceService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConverOrder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\Invoice\ItemFactory as InvoiceItemFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\ItemFactory as ShipmentItemFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Creditmemo\ItemFactory as CreditmemoItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\DB\TransactionFactory;

/**
 * Class SaveOther
 * @package Mageplaza\EditOrder\Model\Order
 */
class SaveOther
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var \Mageplaza\EditOrder\Model\Order\InvoiceService
     */
    protected $_orderInvoiceService;

    /**
     * @var InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var ConverOrder
     */
    protected $converOrder;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var InvoiceItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var CreditmemoItemFactory
     */
    protected $creditmemoItemFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Creditmemo
     */
    protected $creditmemo;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var Shipment
     */
    protected $shipment;

    /**
     * @var ShipmentItemFactory
     */
    protected $shipmentItemFactory;

    /**
     * @var CreditmemoCollection
     */
    protected $creditmemoCollection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var array
     */
    private $_newItemData = [];

    /**
     * @var int
     */
    private $_newInvoiceId = null;

    /**
     * @var boolean
     */
    private $_isHaveInvoce = false;

    /**
     * @var bool
     */
    private $_isChangedData = true;

    /**
     * @var bool
     */
    private $_isCheckCount = false;

    /**
     * @var bool
     */
    private $_isChangeOrder = false;

    /**
     * @var bool
     */
    private $_isRemonvedAllItems = true;

    /**
     * @var bool
     */
    private $_isAddedProcduct = false;

    /**
     * SaveOther constructor.
     * @param HelperData $helperData
     * @param \Mageplaza\EditOrder\Model\Order\InvoiceService $orderInvoiceService
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ConverOrder $converOrder
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceItemFactory $invoiceItemFactory
     * @param CreditmemoItemFactory $creditmemoItemFactory
     * @param DateTime $dateTime
     * @param Creditmemo $creditmemo
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoCollection $creditmemoCollection
     * @param Shipment $shipment
     * @param ShipmentItemFactory $shipmentItemFactory
     */
    public function __construct(
        HelperData $helperData,
        OrderInvoiceService $orderInvoiceService,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        ConverOrder $converOrder,
        InvoiceFactory $invoiceFactory,
        InvoiceItemFactory $invoiceItemFactory,
        CreditmemoItemFactory $creditmemoItemFactory,
        DateTime $dateTime,
        Creditmemo $creditmemo,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoCollection $creditmemoCollection,
        Shipment $shipment,
        ShipmentItemFactory $shipmentItemFactory
    ) {
        $this->_helperData             = $helperData;
        $this->_orderInvoiceService    = $orderInvoiceService;
        $this->_invoiceService         = $invoiceService;
        $this->_transactionFactory     = $transactionFactory;
        $this->_productRepository      = $productRepository;
        $this->_orderRepository        = $orderRepository;
        $this->converOrder             = $converOrder;
        $this->invoiceFactory          = $invoiceFactory;
        $this->invoiceItemFactory      = $invoiceItemFactory;
        $this->creditmemoItemFactory   = $creditmemoItemFactory;
        $this->dateTime                = $dateTime;
        $this->creditmemo              = $creditmemo;
        $this->creditmemoFactory       = $creditmemoFactory;
        $this->creditmemoCollection    = $creditmemoCollection;
        $this->shipment                = $shipment;
        $this->shipmentItemFactory     = $shipmentItemFactory;
    }

    /**
     * @param $order
     * @param array $orderData
     * @param array $diffData
     * @param array $oldOrderIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function creatAndReset($order, $orderData = [], $diffData = [])
    {
        if ($order) {
            $orderStatuses = ['complete','closed'];
            $newOrderIds   = [];
            $orderItems    = [];

            $invoices    = $this->getInvoices($order);
            $shipments   = $this->getShipments($order);
            $creditmemos = $this->getCreditMemos($order);

            if (!empty($invoices) && $this->_isHaveInvoce) {
                $this->_invoiceCreated = false;

                foreach ($order->getAllItems() as $orderItem) {
                    $newOrderIds[] = $orderItem->getProductId();
                    $orderItems[$orderItem->getProductId().'_'.$orderItem->getSku()] = $orderItem->getItemId();

                    if ((isset($orderData['invoiceData'][$orderItem->getProductId()])) &&
                        (float) $orderData['invoiceData'][$orderItem->getProductId()] > (float) $orderItem->getQtyOrdered()) {
                        $orderData['invoiceData'][$orderItem->getProductId()] = $orderItem->getQtyOrdered();
                        $this->_isCheckCount = true;
                    }

                    if ((isset($orderData['invoiceData'][$orderItem->getProductId()])) &&
                        (float) $orderData['invoiceData'][$orderItem->getProductId()] < (float) $orderItem->getQtyOrdered()) {
                        $this->_isChangeOrder = true;
                    }
                }

                $this->checkRemoveAndAddItem($order, $orderData, $diffData, $newOrderIds);

                if (($this->_isChangedData || $this->_isCheckCount) && !$this->_isRemonvedAllItems) {
                    //Creat new invoice with new order
                    $this->createInvoice($order, $orderData);

                    foreach ($order->getAllItems() as $orderItem) {
                        if (!(isset($orderData['invoiceData'][$orderItem->getProductId()]))) {
                            continue;
                        }

                        $qtyInvoiced = (float)$orderData['invoiceData'][$orderItem->getProductId()];
                        $orderItem->setQtyInvoiced($qtyInvoiced);
                        $orderItem->save();
                    }

                    $this->saveNewInvoiceInfo($order, $invoices, $orderItems);
                    if (!empty($shipments)) {
                        $this->saveNewShipmentInfo($shipments, $orderItems);
                    }
                    if (!empty($creditmemos)) {
                        $this->saveNewCreditMemoInfo($order, $creditmemos, $orderItems);
                    }

                    if (in_array($order->getState(), $orderStatuses) || in_array($order->getStatus(), $orderStatuses)) {
                        $isStatus = true;
                        foreach ($creditmemos as $creditmemo) {
                            if ((int) $creditmemo->getState() !== 3) {
                                $isStatus = false;
                            }
                        }

                        if ($isStatus) {
                            $this->_isChangeOrder = true;
                        }
                    }

                    if ($this->_isChangeOrder) {
                        $this->changeOrderStatus($order);
                    }
                } elseif ($this->_isRemonvedAllItems) {
                    $this->cancelISC($order, $invoices, $shipments, $creditmemos);
                } else {
                    $this->changeOrderItem($order, $invoices, $shipments, $creditmemos);
                }
            } elseif (!empty($shipments)) {
                $this->changeInfoWithoutShipment($order, $shipments);
            }
        }
    }

    /**
     * @param $order
     */
    public function changeOrderStatus($order)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        $order->save();
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
            if ((int) $invoice->getState() !== 3) {
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
     * @param $order
     * @param $orderData
     * @param $diffData
     * @param $newOrderIds
     * @return bool
     */
    public function checkRemoveAndAddItem($order, $orderData, $diffData, $newOrderIds)
    {
        $removedItems = $orderData['oldItems'];
        $addedItems   = [];

        foreach ($newOrderIds as $productId) {
            if (($key = array_search($productId, $removedItems)) !== false) {
                unset($removedItems[$key]);
            }
        }

        foreach ($removedItems as $key => $removedItemId) {
            if (!(float) $orderData['invoiceData'][$removedItemId] > 0 && !(float) $orderData['shipmentData'][$removedItemId] > 0) {
                unset($removedItems[$key]);
            }
        }

        foreach ($diffData as $diff) {
            unset($diff['subtotal']);
            unset($diff['row_subtotal']);
            unset($diff['discount_amount']);
            unset($diff['qty']);
            if (isset($diff['action']) && $diff['action'] === 'remove' && empty($removedItems)) {
                unset($diff['action']);
            }

            if (empty($diff) && empty($removedItems)) {
                $this->_isChangedData = false;
            }

            if (isset($diff['sku'])) {
                $addedItems[] = $diff['sku'];
            }
        }

        if (empty($diffData) && empty($removedItems)) {
            $this->_isChangedData = false;
        }

        foreach ($order->getAllItems() as $orderItem) {
            if (in_array($orderItem->getProduct()->getSku(), $addedItems) && empty($removedItems)) {
                $this->_isChangedData = false;
            }
            if (in_array($orderItem->getProductId(), $orderData['oldItems'])) {
                $this->_isRemonvedAllItems = false;
            }
        }

        return $this->_isChangedData;
    }

    /**
     * @param $order
     * @param $invoices
     * @param $orderItems
     * @throws \Exception
     */
    public function saveNewInvoiceInfo($order, $invoices, $orderItems)
    {
        $invoiceItemFactory  = $this->invoiceItemFactory->create();
        $invoiceFactory      = $this->invoiceFactory->create();
        $invoiceShipping     = 0;
        $invoiceBaseShipping = 0;
        $invoiceTax          = 0;
        $invoiceBaseTax      = 0;
        $invoiceTotal        = 0;
        $invoiceBaseSubtotal = 0;
        $invoiceSubtotal     = 0;
        $orderBaseDiscount   = [];
        $orderBaseTax        = [];

        foreach ($invoices as $invoice) {
            $invoiceId = $invoice->getEntityId();

            foreach ($invoice->getItems() as $item) {
                $itemId = $item->getEntityId();
                if ($itemId) {
                    $invoiceItemFactory->load($itemId);
                    if (isset($orderItems[$invoiceItemFactory->getProductId().'_'.$invoiceItemFactory->getSku()])) {
                        $invoiceItemFactory->setOrderItemId($orderItems[$invoiceItemFactory->getProductId().'_'.$invoiceItemFactory->getSku()]);
                        $invoiceItemFactory->save();
                    } else {
                        $invoiceItemFactory->delete();
                    }
                }
            }

            $invoiceFactory->load($invoiceId);
            $invoiceFactory->setState(Invoice::STATE_CANCELED);
            $invoiceFactory->addComment(__('Invoice has been canceled by Mageplaza EditOrder.'));
            $invoiceFactory->save();
        }

        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderBaseDiscount[$orderItem->getProductId()])) {
                $orderItem->setBaseDiscountInvoiced($orderBaseDiscount[$orderItem->getProductId()]);
                $orderItem->setDiscountInvoiced($orderBaseDiscount[$orderItem->getProductId()]);
                $orderItem->setBaseTaxInvoiced($orderBaseTax[$orderItem->getProductId()]);
                $orderItem->setTaxInvoiced($orderBaseTax[$orderItem->getProductId()]);
                $orderItem->save();
            }
        }

        if ($this->_newInvoiceId != null) {
            $newInvoice = $invoiceFactory->load($this->_newInvoiceId);
            $invoiceShipping     = (float) $newInvoice->getShippingAmount();
            $invoiceBaseShipping = (float) $newInvoice->getBaseShippingAmount();
            $invoiceTax          = (float) $newInvoice->getTaxAmount();
            $invoiceBaseTax      = (float) $newInvoice->getBaseTaxAmount();
            $invoiceTotal        = (float) $newInvoice->getGrandTotal();
            $invoiceBaseSubtotal = (float) $newInvoice->getBaseSubtotal();
            $invoiceSubtotal     = (float) $newInvoice->getSubtotal();
        }

        $order->setShippingInvoiced($invoiceShipping);
        $order->setBaseShippingInvoiced($invoiceBaseShipping);
        $order->setTaxInvoiced($invoiceTax);
        $order->setBaseTaxInvoiced($invoiceBaseTax);
        $order->setTotalInvoiced($invoiceTotal);
        $order->setSubtotalInvoiced($invoiceSubtotal);
        $order->setBaseSubtotalInvoiced($invoiceBaseSubtotal);
        $order->save();
    }

    /**
     * @param $shipments
     * @param $orderItems
     * @throws \Exception
     */
    public function saveNewShipmentInfo($shipments, $orderItems)
    {
        $shipmentItemFactory = $this->shipmentItemFactory->create();

        foreach ($shipments as $shipment) {
            if ((int) $shipment->getShipmentStatus() !== 3) {
                foreach ($shipment->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $shipmentItemFactory->load($itemId);
                        if (isset($orderItems[$shipmentItemFactory->getProductId().'_'.$shipmentItemFactory->getSku()])) {
                            $shipmentItemFactory->setOrderItemId($orderItems[$shipmentItemFactory->getProductId().'_'.$shipmentItemFactory->getSku()]);
                            $shipmentItemFactory->save();
                        }
                    }
                }

                $shipment->load($shipment->getEntityId());
                $shipment->setShipmentStatus(3);
                $shipment->addComment(__('Shipment has been canceled by Mageplaza EditOrder.'));
                $shipment->save();
            }
        }
    }

    /**
     * @param $order
     * @param $creditmemos
     * @param $orderItems
     * @throws \Exception
     */
    public function saveNewCreditMemoInfo($order, $creditmemos, $orderItems)
    {
        $creditmemoItemFactory = $this->creditmemoItemFactory->create();
        $creditmemoTotal       = 0;
        $creditmemoBaseTotal   = 0;
        $creditmemoTax         = 0;
        $creditmemoBaseTax     = 0;
        $issetCreditmemos      = false;

        foreach ($creditmemos as $creditmemo) {
            if ((int) $creditmemo->getState() !== 3) {
                foreach ($creditmemo->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $creditmemoItemFactory->load($itemId);
                        if (isset($orderItems[$creditmemoItemFactory->getProductId().'_'.$creditmemoItemFactory->getSku()])) {
                            $creditmemoItemFactory->setOrderItemId($orderItems[$creditmemoItemFactory->getProductId().'_'.$creditmemoItemFactory->getSku()]);
                            $creditmemoItemFactory->save();
                        }
                    }
                }

                $creditmemo->load($creditmemo->getEntityId());
                $creditmemo->setState(Creditmemo::STATE_CANCELED);
                $creditmemo->addComment(__('CreditMemo has been canceled by Mageplaza EditOrder.'));
                $creditmemo->save();
            }
        }

        foreach ($creditmemos as $creditmemo) {
            if ((int)$creditmemo->getState() !== 3) {
                $creditmemoBaseTotal += (float)$creditmemo->getBaseGrandTotal();
                $creditmemoTotal     += (float)$creditmemo->getGrandTotal();
                $creditmemoTax       += (float)$creditmemo->getTaxAmount();
                $creditmemoBaseTax   += (float)$creditmemo->getBaseTaxAmount();
                $issetCreditmemos     = true;
            }
        }

        $order->setBaseTotalRefunded($creditmemoBaseTotal);
        $order->setTotalRefunded($creditmemoTotal);
        $order->setBaseTotalOfflineRefunded($creditmemoBaseTotal);
        $order->setTotalOfflineRefunded($creditmemoTotal);
        $order->setBaseTaxRefunded($creditmemoBaseTax);
        $order->setTaxRefunded($creditmemoTax);

        if (!$issetCreditmemos) {
            $order->setBaseShippingRefunded(0);
            $order->setShippingRefunded(0);
        }

        $order->save();
    }

    /**
     * @param $order
     * @param $shipments
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeInfoWithoutShipment($order, $shipments)
    {
        $orderItemIds = [];
        $orderItemQty = [];

        foreach ($order->getAllItems() as $orderItem) {
            $orderItemIds[$orderItem->getProductId()] = $orderItem->getItemId();
            $orderItemQty[$orderItem->getProductId()] = $orderItem->getQtyOrdered();
        }

        $shipmentItemFactory = $this->shipmentItemFactory->create();
        $shippingData        = [];
        $isStatus            = false;

        foreach ($shipments as $shipment) {
            if ((int) $shipment->getShipmentStatus() !== 3) {
                $shippingTotal       = 0;
                foreach ($shipment->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $shipmentItemFactory->load($itemId);
                        if (isset($orderItemIds[$shipmentItemFactory->getProductId()])) {
                            $shipmentItemFactory->setOrderItemId($orderItemIds[$shipmentItemFactory->getProductId()]);
                            if ($orderItemQty[$shipmentItemFactory->getProductId()] < $shipment->getQty()) {
                                $shipmentItemFactory->setQty($orderItemQty[$shipmentItemFactory->getProductId()]);
                            }
                            $shipmentItemFactory->save();
                            $isStatus       = true;
                            $shippingTotal += $orderItemQty[$shipmentItemFactory->getProductId()];
                            $shippingData[$shipment->getId()][$shipmentItemFactory->getProductId()] = $shipmentItemFactory->getQty();
                        }
                    }
                }

                $shipment->load($shipment->getEntityId());
                $shipment->setTotalQty($shippingTotal);
                $shipment->addComment(__('Shipment has been changed by Mageplaza EditOrder.'));
                $shipment->save();
            }
        }

        if ($isStatus) {
            foreach ($order->getAllItems() as $orderItem) {
                $shipTotalQty = 0;
                foreach ($shippingData as $shipment) {
                    if (isset($shipment[$orderItem->getProductId()])) {
                        $shipTotalQty += (float)$shipment[$orderItem->getProductId()];
                    }
                }

                $orderItem->setQtyShipped($shipTotalQty);
                $orderItem->save();

            }
        }
    }

    /**
     * @param $order
     * @param array $orderData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice($order, $orderData = [])
    {
        $baseGrandTotal = 0;
        $grandTotal     = 0;
        $baseSubTotal   = 0;
        $subTotal       = 0;
        $baseTaxAmount  = 0;
        $taxAmount      = 0;

        $invoice = $this->_orderInvoiceService->prepareInvoice($order, [], $orderData['invoiceData']);
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);

        foreach ($invoice->getItems() as $item) {
            $product = $this->_productRepository->getById($item->getProductId());
            if ($product->getTypeId() === 'bundle') {
                continue;
            }

            $baseGrandTotal += $item->getBaseRowTotal();
            $grandTotal     += $item->getRowTotal();
            $baseSubTotal   += $item->getBasePrice() * $item->getQty();
            $subTotal       += $item->getPrice() * $item->getQty();
            $baseTaxAmount  += $item->getBaseTaxAmount();
            $taxAmount      += $item->getTaxAmount();
            $this->_newItemData[$item->getProductId()] = $item->getOrderItemId();
        }

        $invoice->setTaxAmount($taxAmount);
        $invoice->setShippingAmount($order->getData('shipping_amount'));
        $invoice->setBaseSubTotal($baseSubTotal);
        $invoice->setSubTotal($subTotal);

        $grandBaseTotal = $baseSubTotal + $invoice->getBaseDiscountAmount() + $baseTaxAmount + $order->getData('shipping_amount');
        $grandTotal     = $subTotal + $invoice->getDiscountAmount() + $taxAmount + $order->getData('shipping_amount');
        $invoice->setGrandTotal($grandTotal);
        $invoice->setBaseGrandTotal($grandBaseTotal);
        $invoice->addComment(__('Invoice has been created by Mageplaza EditOrder.'));

        try {
            if ($subTotal > 0) {
                $transactionSave = $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->_newInvoiceId = $invoice->getId();
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @param $order
     * @param array $invoices
     * @param array $shipments
     * @param array $creditmemos
     * @throws \Exception
     */
    public function cancelISC($order, $invoices = [], $shipments = [], $creditmemos = [])
    {
        //Invoice
        $invoiceFactory      = $this->invoiceFactory->create();

        foreach ($invoices as $invoice) {
            $invoiceFactory->load($invoice->getEntityId());
            $invoiceFactory->setState(Invoice::STATE_CANCELED);
            $invoiceFactory->addComment(__('Invoice has been canceled by Mageplaza EditOrder.'));
            $invoiceFactory->save();
        }

        $order->setShippingInvoiced(0);
        $order->setBaseShippingInvoiced(0);
        $order->setTaxInvoiced(0);
        $order->setBaseTaxInvoiced(0);
        $order->setTotalInvoiced(0);
        $order->setSubtotalInvoiced(0);
        $order->setBaseSubtotalInvoiced(0);
        $order->save();


        //Shipment
        if (!empty($shipments)) {
            foreach ($shipments as $shipment) {
                if ((int) $shipment->getShipmentStatus() !== 3) {
                    $shipment->load($shipment->getEntityId());
                    $shipment->setShipmentStatus(3);
                    $shipment->addComment(__('Shipment has been canceled by Mageplaza EditOrder.'));
                    $shipment->save();
                }
            }
        }

        //Creditmemo
        if (!empty($creditmemos)) {
            foreach ($creditmemos as $creditmemo) {
                if ((int) $creditmemo->getState() !== 3) {
                    $creditmemo->load($creditmemo->getEntityId());
                    $creditmemo->setState(Creditmemo::STATE_CANCELED);
                    $creditmemo->addComment(__('CreditMemo has been canceled by Mageplaza EditOrder.'));
                    $creditmemo->save();
                }
            }

            $order->setBaseTotalRefunded(0);
            $order->setTotalRefunded(0);
            $order->setBaseTotalOfflineRefunded(0);
            $order->setTotalOfflineRefunded(0);
            $order->setBaseTaxRefunded(0);
            $order->setTaxRefunded(0);
            $order->setBaseShippingRefunded(0);
            $order->setShippingRefunded(0);
            $order->save();
        }

        //Change order status
        if ($order->getState() !== 'processing') {
            $this->changeOrderStatus($order);
        }
    }

    /**
     * @param $order
     * @param array $invoices
     * @param array $shipments
     * @param array $creditmemos
     * @throws \Exception
     */
    public function changeOrderItem($order, $invoices = [], $shipments = [], $creditmemos = [])
    {
        $orderItemIds  = [];
        $orderItemData = [];

        foreach ($order->getAllItems() as $orderItem) {
            $orderItemIds[$orderItem->getProductId()]              = $orderItem->getItemId();
            $orderItemData['qty'][$orderItem->getProductId()]      = $orderItem->getQtyOrdered();
            $orderItemData['base_tax'][$orderItem->getProductId()] = $orderItem->getBaseTaxAmount();
            $orderItemData['tax'][$orderItem->getProductId()]      = $orderItem->getTaxAmount();
        }

        if (!empty($invoices)) {
            $this->updateInvoiceInfo($order, $invoices, $orderItemIds, $orderItemData);
        }
        if (!empty($shipments)) {
            $this->updateShipmentInfo($order, $shipments, $orderItemIds);
        }
        if (!empty($creditmemos)) {
            $this->updateCreditMemoInfo($order, $creditmemos, $orderItemIds);
        }

        $this->changeOrderStatus($order);
    }

    /**
     * @param $order
     * @param $invoices
     * @param $orderItemIds
     * @param $orderItemData
     * @throws \Exception
     */
    public function updateInvoiceInfo($order, $invoices, $orderItemIds, $orderItemData)
    {
        $invoiceItemFactory = $this->invoiceItemFactory->create();
        $invoiceFactory     = $this->invoiceFactory->create();
        $orderQty           = [];
        $orderBaseDiscount  = [];
        $orderBaseTax       = [];
        $orderDiscount      = [];
        $orderTax           = [];
        $diffBaseValueTotal = 0;
        $diffValueTotal     = 0;

        foreach ($invoices as $invoice) {
            if ((int)$invoice->getState() !== 3) {
                $invoiceId    = $invoice->getEntityId();
                $baseTaxTotal = 0;
                $taxTotal     = 0;
                $isChangedTax = false;

                foreach ($invoice->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $invoiceItemFactory->load($itemId);
                        if (isset($orderItemIds[$invoiceItemFactory->getProductId()])) {
                            $checkTax          = false;
                            $valueBaseTaxTotal = 0;
                            $valueTaxTotal     = 0;

                            $invoiceItemFactory->setOrderItemId($orderItemIds[$invoiceItemFactory->getProductId()]);
                            $invoiceItemFactory->save();

                            if (isset($orderItemData['tax'][$invoiceItemFactory->getProductId()]) &&
                                isset($orderItemData['qty'][$invoiceItemFactory->getProductId()])) {
                                if ((float)$orderItemData['tax'][$invoiceItemFactory->getProductId()] !== (float)$invoiceItemFactory->getTaxAmount()) {
                                    if ((int)$orderItemData['qty'][$invoiceItemFactory->getProductId()] === (int)$invoiceItemFactory->getQty()) {
                                        $invoiceItemFactory->setBaseTaxAmount((float)$orderItemData['base_tax'][$invoiceItemFactory->getProductId()]);
                                        $invoiceItemFactory->setTaxAmount((float)$orderItemData['tax'][$invoiceItemFactory->getProductId()]);
                                        $invoiceItemFactory->save();
                                    } else {
                                        $taxPercent = (float)$invoiceItemFactory->getOrderItem()->getTaxAmount() / (float)$invoiceItemFactory->getOrderItem()->getPrice() / (int)$invoiceItemFactory->getOrderItem()->getQtyOrdered();
                                        $baseTaxAmount  = (int)$invoiceItemFactory->getQty() * (float)$invoiceItemFactory->getBasePrice() * $taxPercent;
                                        $taxAmount      = (int)$invoiceItemFactory->getQty() * (float)$invoiceItemFactory->getPrice() * $taxPercent;
                                        $invoiceItemFactory->setBaseTaxAmount($baseTaxAmount);
                                        $invoiceItemFactory->setTaxAmount($taxAmount);
                                        $invoiceItemFactory->save();
                                        $valueBaseTaxTotal = $baseTaxAmount;
                                        $valueTaxTotal     = $taxAmount;
                                        $checkTax          = true;
                                    }
                                    $isChangedTax = true;
                                }

                                if ($checkTax) {
                                    $baseTaxTotal += $valueBaseTaxTotal;
                                    $taxTotal     += $valueTaxTotal;
                                } else {
                                    $baseTaxTotal += (float)$orderItemData['tax'][$invoiceItemFactory->getProductId()];
                                    $taxTotal     += (float)$orderItemData['tax'][$invoiceItemFactory->getProductId()];
                                }
                            }
                            if (isset($orderQty[$invoiceItemFactory->getProductId()])) {
                                $orderQty[$invoiceItemFactory->getProductId()] = (int)$orderQty[$invoiceItemFactory->getProductId()] + (int)$invoiceItemFactory->getQty();
                            } else {
                                $orderQty[$invoiceItemFactory->getProductId()] = $invoiceItemFactory->getQty();
                            }

                            $orderBaseDiscount[$invoiceItemFactory->getProductId()] = $invoiceItemFactory->getBaseDiscountAmount();
                            $orderBaseTax[$invoiceItemFactory->getProductId()]      = $invoiceItemFactory->getBaseTaxAmount();
                            $orderDiscount[$invoiceItemFactory->getProductId()]     = $invoiceItemFactory->getDiscountAmount();
                            $orderTax[$invoiceItemFactory->getProductId()]          = $invoiceItemFactory->getTaxAmount();
                        }
                    }
                }

                $invoiceFactory->load($invoiceId);
                if ($isChangedTax) {
                    $oldBaseTaxAmount = $invoiceFactory->getBaseTaxAmount();
                    $oldTaxAmount     = $invoiceFactory->getTaxAmount();
                    $diffBaseValue    = (float)$baseTaxTotal - (float)$oldBaseTaxAmount;
                    $diffValue        = (float)$taxTotal - (float)$oldTaxAmount;
                    $invoiceFactory->setBaseTaxAmount($baseTaxTotal);
                    $invoiceFactory->setTaxAmount($taxTotal);
                    $invoiceFactory->setBaseGrandTotal($invoiceFactory->getBaseGrandTotal() + $diffBaseValue);
                    $invoiceFactory->setGrandTotal($invoiceFactory->getGrandTotal() + $diffValue);
                    $diffBaseValueTotal += $diffBaseValue;
                    $diffValueTotal     += $diffValue;
                }

                $invoiceFactory->addComment(__('Invoice has been changed by Mageplaza EditOrder.'));
                $invoiceFactory->save();
            }
        }

        if ($diffValueTotal !== 0) {
            $order->setTotalInvoiced($order->getTotalInvoiced() + $diffValueTotal);
            $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() + $diffBaseValueTotal);
            $order->setTaxInvoiced($order->getTaxInvoiced() + $diffValueTotal);
            $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() + $diffBaseValueTotal);
            $order->save();
        }

        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderQty[$orderItem->getProductId()])) {
                $orderItem->setQtyInvoiced($orderQty[$orderItem->getProductId()]);
                $orderItem->setBaseDiscountInvoiced($orderBaseDiscount[$orderItem->getProductId()]);
                $orderItem->setDiscountInvoiced($orderDiscount[$orderItem->getProductId()]);
                $orderItem->setBaseTaxInvoiced($orderBaseTax[$orderItem->getProductId()]);
                $orderItem->setTaxInvoiced($orderTax[$orderItem->getProductId()]);
                $orderItem->save();
            }
        }
    }

    /**
     * @param $order
     * @param $shipments
     * @param $orderItemIds
     * @throws \Exception
     */
    public function updateShipmentInfo($order, $shipments, $orderItemIds)
    {
        $shipmentItemFactory = $this->shipmentItemFactory->create();
        $orderQty            = [];
        $isStatus            = false;

        foreach ($shipments as $shipment) {
            if ((int) $shipment->getShipmentStatus() !== 3) {
                foreach ($shipment->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $shipmentItemFactory->load($itemId);
                        if (isset($orderItemIds[$shipmentItemFactory->getProductId()])) {
                            $shipmentItemFactory->setOrderItemId($orderItemIds[$shipmentItemFactory->getProductId()]);
                            $shipmentItemFactory->save();

                            if (isset($orderQty[$shipmentItemFactory->getProductId()])) {
                                $orderQty[$shipmentItemFactory->getProductId()] = (int) $orderQty[$shipmentItemFactory->getProductId()] + (int) $shipmentItemFactory->getQty();
                            } else {
                                $orderQty[$shipmentItemFactory->getProductId()] = $shipmentItemFactory->getQty();
                            }

                            $isStatus = true;
                        }
                    }
                }

                $shipment->load($shipment->getEntityId());
                $shipment->addComment(__('Shipment has been changed by Mageplaza EditOrder.'));
                $shipment->save();
            }
        }

        if ($isStatus) {
            foreach ($order->getAllItems() as $orderItem) {
                if (isset($orderQty[$orderItem->getProductId()])) {
                    $orderItem->setQtyShipped($orderQty[$orderItem->getProductId()]);
                    $orderItem->save();
                }
            }
        }
    }

    /**
     * @param $order
     * @param $creditmemos
     * @param $orderItemIds
     * @throws \Exception
     */
    public function updateCreditMemoInfo($order, $creditmemos, $orderItemIds)
    {
        $creditmemoItemFactory = $this->creditmemoItemFactory->create();
        $orderQty              = [];
        $isStatus              = false;
        $creditmemoTotal       = 0;
        $creditmemoTax         = 0;
        $creditmemoBaseTax     = 0;


        foreach ($creditmemos as $creditmemo) {
            if ((int) $creditmemo->getState() !== 3) {
                foreach ($creditmemo->getItems() as $item) {
                    $itemId = $item->getEntityId();
                    if ($itemId) {
                        $creditmemoItemFactory->load($itemId);
                        if (isset($orderItemIds[$creditmemoItemFactory->getProductId()])) {
                            $creditmemoItemFactory->setOrderItemId($orderItemIds[$creditmemoItemFactory->getProductId()]);
                            $creditmemoItemFactory->save();

                            if (isset($orderQty[$creditmemoItemFactory->getProductId()])) {
                                $orderQty[$creditmemoItemFactory->getProductId()] = (int) $orderQty[$creditmemoItemFactory->getProductId()] + (int) $creditmemoItemFactory->getQty();
                            } else {
                                $orderQty[$creditmemoItemFactory->getProductId()] = $creditmemoItemFactory->getQty();
                            }

                            $isStatus = true;
                        }
                    }
                }

                $creditmemo->load($creditmemo->getEntityId());
                $creditmemo->addComment(__('CreditMemo has been changed by Mageplaza EditOrder.'));
                $creditmemo->save();
            }
        }

        foreach ($creditmemos as $creditmemo) {
            if ((int) $creditmemo->getState() !== 3) {
                $creditmemoTotal   += (float)$creditmemo->getGrandTotal();
                $creditmemoTax     += (float)$creditmemo->getTaxAmount();
                $creditmemoBaseTax += (float)$creditmemo->getBaseTaxAmount();
            }
        }

        $order->setTotalOfflineRefunded($creditmemoTotal);
        $order->setBaseTotalRefunded($creditmemoTotal);
        $order->setTaxRefunded($creditmemoTax);
        $order->setBaseTaxRefunded($creditmemoBaseTax);
        $order->save();

        if ($isStatus) {
            foreach ($order->getAllItems() as $orderItem) {
                if (isset($orderQty[$orderItem->getProductId()])) {
                    $orderItem->setQtyRefunded($orderQty[$orderItem->getProductId()]);
                    $orderItem->save();
                }
            }
        }
    }


    /**
     * @param $order
     * @param array $invoices
     * @param array $shipments
     * @param array $creditmemos
     * @param array $orderData
     * @param array $diffData
     */
    public function updateOrder($order, $invoices = [], $shipments = [], $creditmemos = [], $orderData = [], $diffData = [])
    {

        $orderStatuses = ['complete','closed'];
        $orderItemIds  = [];
        $newOrderState=['pending','new'];

        foreach ($order->getAllItems() as $orderItem) {
            $orderItemIds[$orderItem->getProductId()] = $orderItem->getItemId();

            if ((isset($orderData['invoiceData'][$orderItem->getProductId()])) &&
                (float) $orderData['invoiceData'][$orderItem->getProductId()] < (float) $orderItem->getQtyOrdered()) {
                if (in_array($order->getState(), $orderStatuses) || in_array($order->getStatus(), $orderStatuses)) {
                    $this->_isCheckCount = true;
                }
                $this->_isChangeOrder = true;
            }

            if (in_array($orderItem->getProductId(), $orderData['oldItems'])) {
                $this->_isRemonvedAllItems = false;
            }
        }

        foreach ($diffData as $diff) {
            unset($diff['subtotal']);
            unset($diff['row_subtotal']);
            unset($diff['discount_amount']);
            unset($diff['qty']);

            if (!empty($diff)) {
                $this->_isAddedProcduct = true;
            }
        }

        if (!empty($invoices)) {
            $this->updateOrderInvoice($order, $invoices, $orderItemIds);
        }
        if (!empty($shipments)) {
            $this->updateOrderShipment($order, $shipments, $orderItemIds);
        }
        if (!empty($creditmemos)) {
            $this->updateOrderCreditMemo($order, $creditmemos, $orderItemIds);
        }

        if (!in_array($order->getState(), $newOrderState) && ($this->_isChangeOrder || $this->_isRemonvedAllItems || $this->_isAddedProcduct)) {
            $this->changeOrderStatus($order);
        }
    }

    /**
     * @param $order
     * @param $invoices
     * @param $orderItemIds
     */
    public function updateOrderInvoice($order, $invoices, $orderItemIds)
    {
        $orderQty           = [];
        $orderBaseDiscount  = [];
        $orderBaseTax       = [];
        $orderDiscount      = [];
        $orderTax           = [];

        foreach ($invoices as $invoice) {
            if ((int) $invoice->getState() !== 3) {
                foreach ($invoice->getItems() as $item) {
                    if (isset($orderItemIds[$item->getProductId()])) {
                        if (!isset($orderQty[$item->getProductId()])) {
                            $orderQty[$item->getProductId()] = $item->getQty();
                        } else {
                            $preQty = $orderQty[$item->getProductId()];
                            $newQty = $preQty + $item->getQty();
                            $orderQty[$item->getProductId()] = $newQty;
                        }

                        $orderBaseDiscount[$item->getProductId()] = $item->getBaseDiscountAmount();
                        $orderBaseTax[$item->getProductId()] = $item->getBaseTaxAmount();
                        $orderDiscount[$item->getProductId()] = $item->getDiscountAmount();
                        $orderTax[$item->getProductId()] = $item->getTaxAmount();
                    }
                }
            }
        }

        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderQty[$orderItem->getProductId()])) {
                $orderItem->setQtyInvoiced($orderQty[$orderItem->getProductId()]);
                $orderItem->setBaseDiscountInvoiced($orderBaseDiscount[$orderItem->getProductId()]);
                $orderItem->setDiscountInvoiced($orderDiscount[$orderItem->getProductId()]);
                $orderItem->setBaseTaxInvoiced($orderBaseTax[$orderItem->getProductId()]);
                $orderItem->setTaxInvoiced($orderTax[$orderItem->getProductId()]);
                $orderItem->save();
            }
        }
    }

    /**
     * @param $order
     * @param $shipments
     * @param $orderItemIds
     */
    public function updateOrderShipment($order, $shipments, $orderItemIds)
    {
        $shippingData = [];
        $isStatus     = false;

        foreach ($shipments as $shipment) {
            if ((int) $shipment->getShipmentStatus() !== 3) {
                foreach ($shipment->getItems() as $item) {
                    if (isset($orderItemIds[$item->getProductId()])) {
                        $shippingData[$shipment->getId()][$item->getProductId()] = $item->getQty();
                        $isStatus = true;
                    }
                }
            }
        }

        if ($isStatus) {
            foreach ($order->getAllItems() as $orderItem) {
                $shipTotalQty = 0;
                foreach ($shippingData as $shipment) {
                    if (isset($shipment[$orderItem->getProductId()])) {
                        $shipTotalQty += (float)$shipment[$orderItem->getProductId()];
                    }
                }

                $orderItem->setQtyShipped($shipTotalQty);
                $orderItem->save();
            }
        }
    }

    /**
     * @param $order
     * @param $creditmemos
     * @param $orderItemIds
     */
    public function updateOrderCreditMemo($order, $creditmemos, $orderItemIds)
    {
        $orderQty              = [];
        $isStatus              = false;
        $creditmemoTotal       = 0;
        $creditmemoTax         = 0;
        $creditmemoBaseTax     = 0;

        foreach ($creditmemos as $creditmemo) {
            if ((int) $creditmemo->getState() !== 3) {
                foreach ($creditmemo->getItems() as $item) {
                    if (isset($orderItemIds[$item->getProductId()])) {
                        if (!isset($orderQty[$item->getProductId()])) {
                            $orderQty[$item->getProductId()] = $item->getQty();
                        } else {
                            $preQty = $orderQty[$item->getProductId()];
                            $newQty = $preQty + $item->getQty();
                            $orderQty[$item->getProductId()] = $newQty;
                        }

                        $isStatus = true;
                    }
                }

                $creditmemoTotal   += (float)$creditmemo->getGrandTotal();
                $creditmemoTax     += (float)$creditmemo->getTaxAmount();
                $creditmemoBaseTax += (float)$creditmemo->getBaseTaxAmount();
            }
        }

        $order->setTotalOfflineRefunded($creditmemoTotal);
        $order->setBaseTotalRefunded($creditmemoTotal);
        $order->setTaxRefunded($creditmemoTax);
        $order->setBaseTaxRefunded($creditmemoBaseTax);
        $order->save();

        if ($isStatus) {
            foreach ($order->getAllItems() as $orderItem) {
                if (isset($orderQty[$orderItem->getProductId()])) {
                    $orderItem->setQtyRefunded($orderQty[$orderItem->getProductId()]);
                    $orderItem->save();
                }
            }
        }
    }
}
