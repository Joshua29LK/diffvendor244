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

use Exception;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item as ResourceItem;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo\ItemFactory as CreditmemoItemFactory;
use Magento\Sales\Model\Order\Invoice\ItemFactory as InvoiceItemFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory as ShipmentItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\RuleRepository;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Quote\IsSalableWithReservationsCondition as EditOrderIsSalableWithReservationsCondition;
use Mageplaza\EditOrder\Model\Quote\QuoteManagement;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Total
 * @package Mageplaza\EditOrder\Model\Order
 */
class Total
{
    const TYPE_COLLECT_ITEMS    = 'items';
    const TYPE_COLLECT_SHIPPING = 'shipping';

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var StockItemInterface
     */
    protected $stockItem;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ResourceItem
     */
    protected $itemResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    protected $_cartItemFactory;

    /**
     * @var InvoiceItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var CreditmemoItemFactory
     */
    protected $creditmemoItemFactory;

    /**
     * @var ShipmentItemFactory
     */
    protected $shipmentItemFactory;

    /**
     * @var ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var SaveOther
     */
    protected $saveOther;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var RuleRepository
     */
    protected $ruleRepostitory;

    /**
     * @param QuoteManagement $quoteManagement
     * @param QuoteFactory $quoteFactory
     * @param QuoteSession $quoteSession
     * @param OrderManagement $orderManagement
     * @param StockItemInterface $stockItem
     * @param HelperData $helperData
     * @param ResourceItem $itemResourceModel
     * @param ResourceConnection $resourceConnection
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartItemInterfaceFactory $cartItemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param InvoiceItemFactory $invoiceItemFactory
     * @param CreditmemoItemFactory $creditmemoItemFactory
     * @param ShipmentItemFactory $shipmentItemFactory
     * @param ItemFactory $orderItemFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param StockRegistryInterface $stockRegistry
     * @param SaveOther $saveOther
     * @param Coupon $coupon
     * @param RuleRepository $ruleRepostitory
     */
    public function __construct(
        QuoteManagement $quoteManagement,
        QuoteFactory $quoteFactory,
        QuoteSession $quoteSession,
        OrderManagement $orderManagement,
        StockItemInterface $stockItem,
        HelperData $helperData,
        ResourceItem $itemResourceModel,
        ResourceConnection $resourceConnection,
        PriceCurrencyInterface $priceCurrency,
        CartItemInterfaceFactory $cartItemFactory,
        ProductRepositoryInterface $productRepository,
        InvoiceItemFactory $invoiceItemFactory,
        CreditmemoItemFactory $creditmemoItemFactory,
        ShipmentItemFactory $shipmentItemFactory,
        ItemFactory $orderItemFactory,
        ItemCollectionFactory $itemCollectionFactory,
        StockRegistryInterface $stockRegistry,
        SaveOther $saveOther,
        Coupon $coupon,
        RuleRepository $ruleRepostitory
    ) {
        $this->quoteManagement       = $quoteManagement;
        $this->quoteFactory          = $quoteFactory;
        $this->quoteSession          = $quoteSession;
        $this->orderManagement       = $orderManagement;
        $this->stockItem             = $stockItem;
        $this->_helperData           = $helperData;
        $this->itemResourceModel     = $itemResourceModel;
        $this->resourceConnection    = $resourceConnection;
        $this->priceCurrency         = $priceCurrency;
        $this->_cartItemFactory      = $cartItemFactory;
        $this->_productRepository    = $productRepository;
        $this->invoiceItemFactory    = $invoiceItemFactory;
        $this->creditmemoItemFactory = $creditmemoItemFactory;
        $this->shipmentItemFactory   = $shipmentItemFactory;
        $this->orderItemFactory      = $orderItemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->stockRegistry         = $stockRegistry;
        $this->saveOther             = $saveOther;
        $this->coupon                = $coupon;
        $this->ruleRepostitory       = $ruleRepostitory;
    }

    /**
     * @param Order $order
     * @param array $data
     * @param array $qty
     *
     * @return array
     * @throws Exception
     */
    public function saveOrder($order, $data, $qty = [])
    {
        $origItems = $oldShippedQty = [];
        $newWeight = 0;
        foreach ($order->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $origItem = $this->orderItemFactory->create();
            $origItem->setData($item->getData());
            $origItems[] = $origItem;
            $oldShippedQty[$item->getSku()] = $item->getQtyShipped();
        }

        $total = $this->collectTotals($order, $data);

        $newOrderIds  = [];
        $isChanged = null;
        $isCheckCount = false;
        if (array_key_exists('orderData', $data)) {
            $orderData = $data['orderData'];
            foreach ($order->getAllItems() as $orderItem) {
                $newOrderIds[] = $orderItem->getProductId();
                if ((isset($orderData['invoiceData'][$orderItem->getProductId()])) &&
                    (float)$orderData['invoiceData'][$orderItem->getProductId()] > (float)$orderItem->getQtyOrdered()) {
                    $orderData['invoiceData'][$orderItem->getProductId()] = $orderItem->getQtyOrdered();
                    $isCheckCount = true;
                }
            }

            $isChanged = $this->saveOther->checkRemoveAndAddItem($order, $data['orderData'], $data['diffData'], $newOrderIds);
        }
        $isChanged = $isChanged || $isCheckCount;

        if (count($total)) {
            $storeId = $order->getStore()->getStoreId();

            if ($data['type'] === self::TYPE_COLLECT_ITEMS) {
                $objectManager        = ObjectManager::getInstance();
                $resetSalableQtyItems = $returnStockItems = $itemStockStatus = $requestedQuantities = [];

                if (!empty($qty)) {
                    foreach ($order->getItems() as $orderItem) {
                        if ($orderItem->getParentItemId() || $orderItem->getProductType() === Type::TYPE_BUNDLE) {
                            continue;
                        }

                        $stockItem = $this->stockRegistry->getStockItem($orderItem->getProductId());
                        $stockStatus = (bool)$stockItem->getIsInStock();
                        $requestedQty = (float)$orderItem->getQtyOrdered() - (float)($qty[$orderItem->getSku()] ?? 0);
                        $requestedQuantities[$orderItem->getSku()] = $requestedQty;

                        if ($stockStatus === false && $requestedQty <= 0) {
                            $stockItem->setIsInStock(1);
                            $stockItem->setQty($stockItem->getQty() + 1);
                            $stockItem->save();
                            $itemStockStatus[$orderItem->getProductId()] = $requestedQty < 0 ? 1 : 0;
                        }

                        if ($order->getStatus() === 'pending') {
                            if (isset($qty[$orderItem->getSku()])) {
                                $resetSalableQtyItems[] = [
                                    'item' => $orderItem,
                                    'qty'  => $qty[$orderItem->getSku()]
                                ];
                            }
                        } else {
                            $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];

                            $shippedQty = (float) $orderItem->getQtyShipped();
                            $oldQty     = (float) ($qty[$orderItem->getSku()] ?? 0);
                            $newQty     = (float) $orderItem->getQtyOrdered();

                            if ($oldQty >= $newQty) {
                                $backToSalableQty = $newQty >= $shippedQty ? $oldQty - $newQty : $oldQty - $shippedQty;
                                if ($backToSalableQty > 0) {
                                    $resetSalableQtyItems[] = [
                                        'item' => $orderItem,
                                        'qty'  => $backToSalableQty
                                    ];
                                }

                                $backToStockQty = $oldQty <= $shippedQty ? $oldQty - $newQty : $shippedQty - $newQty;
                                if ($updateAfterEditingConfig === 1
                                    && (float) $oldShippedQty[$orderItem->getSku()] > 0
                                    && !$orderItem->isDeleted()
                                    && $isChanged
                                ) {
                                    $backToStockQty = $oldShippedQty[$orderItem->getSku()];
                                    $resetSalableQtyItems[] = [
                                        'item' => $orderItem,
                                        'qty'  => -$backToStockQty
                                    ];
                                }
                                if ($backToStockQty > 0) {
                                    $returnStockItems [] = [
                                        'item'            => $orderItem,
                                        'backToStockQty'  => $backToStockQty,
                                        'newShippedQty'   => $newQty
                                    ];
                                }
                            } else {
                                $subtractFromSalableQty = $newQty - $oldQty;
                                $resetSalableQtyItems[] = [
                                    'item' => $orderItem,
                                    'qty'  => -$subtractFromSalableQty
                                ];
                            }
                        }
                    }
                    if ($this->isEnableInventorySales()) {
                        try {
                            $isSalable = $objectManager->create(
                                EditOrderIsSalableWithReservationsCondition::class
                            );

                            foreach ($requestedQuantities as $itemSku => $qty) {
                                $stockId = $this->stockItem->getStockId();
                                $result = $isSalable->execute(
                                    $itemSku,
                                    $stockId,
                                    $qty
                                );

                                if ($result->getErrors()) {
                                    throw new LocalizedException(__($result->getErrors()[0]->getMessage()));
                                }
                            }
                        } catch (Exception $e) {
                            $order->setItems($origItems);

                            $order->save();

                            if (!empty($itemStockStatus)) {
                                foreach ($itemStockStatus as $productId => $status) {
                                    $stockItem = $this->stockRegistry->getStockItem($productId);
                                    if (!$status) {
                                        $stockItem->setIsInStock(0);
                                        $stockItem->save();
                                    }
                                }
                            }

                            return [
                                'error' => $e->getMessage()
                            ];
                        }
                    }

                    if (!empty($resetSalableQtyItems)) {
                        foreach ($resetSalableQtyItems as $item) {
                            $this->resetSalableQuantity($item['item'], $item['qty']);
                        }
                    }
                    if (!empty($returnStockItems) && $this->_helperData->isReturnItemToStock()) {
                        foreach ($returnStockItems as $item) {
                            $stockItem = $this->stockRegistry->getStockItemBySku($item['item']->getSku());
                            $stockItem->setQty($stockItem->getQty() + $item['backToStockQty']);
                            $this->stockRegistry->updateStockItemBySku($item['item']->getSku(), $stockItem);
                        }
                    }
                }
                if ($order->getStatus() === 'pending') {
                    $this->orderManagement->place($order);
                } else {
                    $order->save();
                }
                if (!empty($itemStockStatus)) {
                    foreach ($itemStockStatus as $productId => $status) {
                        $stockItem = $this->stockRegistry->getStockItem($productId);
                        $stockItem->setQty($stockItem->getQty() - 1);
                        if (!$status) {
                            $stockItem->setIsInStock(0);
                        }
                        $stockItem->save();
                    }
                }
            } else {
                $order->save();
            }

            if (isset($total['ship_amount'])) {
                $order
                    ->setShippingMethod($data['method'])
                    ->setShippingDescription($total['ship_description'])
                    ->setBaseShippingAmount($total['ship_amount'])
                    ->setBaseShippingTaxAmount($total['ship_tax_amount'])
                    ->setBaseShippingDiscountAmount($total['ship_discount_amount'])
                    ->setBaseShippingInclTax($data['total_fee'])
                    ->setShippingAmount($this->priceCurrency->convert($total['ship_amount'], $storeId))
                    ->setShippingTaxAmount($this->priceCurrency->convert($total['ship_tax_amount'], $storeId))
                    ->setShippingDiscountAmount($this->priceCurrency->convert($total['ship_discount_amount'], $storeId))
                    ->setShippingInclTax($this->priceCurrency->convert($data['total_fee'], $storeId));
            }

            $order
                ->setBaseTaxAmount($total['tax_amount'])
                ->setBaseDiscountAmount(-$total['discount_amount'])
                ->setBaseGrandTotal($total['grand_total']);

            $order
                ->setTaxAmount($this->priceCurrency->convert($total['tax_amount'], $storeId))
                ->setDiscountAmount($this->priceCurrency->convert(-$total['discount_amount'], $storeId))
                ->setGrandTotal($this->priceCurrency->convert($total['grand_total'], $storeId));

            if (isset($total['subtotal'])) {
                $order->setSubtotal($this->priceCurrency->convert($total['subtotal'], $storeId));
                $order->setBaseSubtotal($total['subtotal'])
                    ->setSubtotalInclTax($this->priceCurrency->convert($total['subtotal_incl_tax'], $storeId))
                    ->setBaseSubtotalInclTax($total['subtotal_incl_tax']);
            }
        }

        $order->setMpIsEditOrder('1');
        $order->save();

        return [
            'success' => true
        ];
    }

    /**
     * @param $item
     * @param $qty
     */
    public function resetSalableQuantity($item, $qty)
    {
        if (!$this->isEnableInventorySales()) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName  = $this->resourceConnection->getTableName('inventory_reservation');

        $columns = [
            ReservationInterface::STOCK_ID,
            ReservationInterface::SKU,
            ReservationInterface::QUANTITY,
            ReservationInterface::METADATA,
        ];

        $data = [[$this->stockItem->getStockId(), $item->getSku(), $qty, '{}']];
        $connection->insertArray($tableName, $columns, $data);
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function collectTotals($order, $data)
    {
        $totals = [];

        if ($data['type'] === self::TYPE_COLLECT_SHIPPING) {
            $totals = $this->collectTotalsByShipping($order, $data);
        }

        if ($data['type'] === self::TYPE_COLLECT_ITEMS) {
            $totals = $this->collectTotalsByItems($order, $data);
        }

        return $totals;
    }

    /**
     * @param Order $order
     * @param array $shipData
     *
     * @return array
     */
    public function collectTotalsByShipping($order, $shipData)
    {
        $oldShipAmount     = $order->getBaseShippingAmount();
        $oldGrandTotal     = $order->getBaseGrandTotal();
        $oldTaxAmount      = $order->getBaseTaxAmount();
        $oldShipTax        = $order->getBaseShippingTaxAmount();
        $oldDiscountAmount = $order->getBaseDiscountAmount();
        $oldShipDiscount   = $order->getBaseShippingDiscountAmount();

        $newShipTaxAmount  = $shipData['ship_tax_percent'] * $shipData['ship_amount'] / 100;
        $newTaxAmount      = $oldTaxAmount - $oldShipTax + $newShipTaxAmount;
        $newDiscountAmount = -$oldDiscountAmount - $oldShipDiscount + $shipData['ship_discount_amount'];
        $newGrandTotal     = $oldGrandTotal - $oldDiscountAmount -
            $oldTaxAmount + $newTaxAmount - $newDiscountAmount - $oldShipAmount + $shipData['ship_amount'];

        return [
            'ship_amount'          => $shipData['ship_amount'],
            'ship_tax_amount'      => $newShipTaxAmount,
            'ship_discount_amount' => $shipData['ship_discount_amount'],
            'ship_description'     => $shipData['ship_description'],
            'tax_amount'           => $newTaxAmount,
            'discount_amount'      => $newDiscountAmount,
            'grand_total'          => $newGrandTotal
        ];
    }

    /**
     * @param Order $order
     * @param array $itemsData
     *
     * @return array
     * @throws Exception
     */
    public function collectTotalsByItems($order, $itemsData)
    {
        $weight            = 0;
        $shipTax           = $order->getBaseShippingTaxAmount();
        $newTaxAmount      = $itemsData['tax_amount'] + $shipTax;
        $shipDiscount      = $order->getBaseShippingDiscountAmount();
        $newDiscountAmount = $itemsData['discount_amount'] + $shipDiscount;
        $shipAmount        = $order->getBaseShippingAmount();
        $couponCode        = $order->getCouponCode();
        $quote             = $this->quoteFactory->create()->load($this->quoteSession->getQuoteId());
        $removeItem        = [];
        $dataItemSkus      = [];

        if ($couponCode && $this->checkCouponCodeFreeShipping($couponCode)) {
            $newDiscountAmount=$newDiscountAmount + $order->getShippingAmount();
        }

        if (count($this->quoteManagement->getResolveItems($quote)) === 0) {
            $dataItems = $itemsData['items'];
            foreach ($dataItems as $dataItem) {
                $product   = $this->_productRepository->get($dataItem['sku']);
                $quoteItem = $this->_cartItemFactory->create();
                $quoteItem->setProduct($product)->setQty($dataItem['qty']);
                $quote->addItem($quoteItem);
                if ($product->getTypeId() === Type::TYPE_BUNDLE) {
                    $dataItemSkus[] = $product->getSku();
                }
            }
            $quote->collectTotals()->save();
        }

        $orderItemFactory = $this->orderItemFactory->create();
        $itemCollection   = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $order->getId());

        foreach ($itemCollection as $value) {
            if (in_array($value->getSku(), $dataItemSkus)) {
                $orderItemFactory->load($value->getItemId())->delete();
            }
        }

        $quoteSku   = [];
        $orderItems = [];
        $quoteItems = [];
        $orderSku   = [];

        if (!empty($this->quoteManagement->getResolveItems($quote))) {
            foreach ($this->quoteManagement->getResolveItems($quote) as $quoteItem) {
                $quoteSku[]                       = $quoteItem->getSku();
                $quoteItems[$quoteItem->getSku()] = $quoteItem;
            }
        }

        foreach ($order->getAllItems() as $item) {
            $orderSku[]                  = $item->getSku();
            $orderItems[$item->getSku()] = $item;
        }

        $diffSku  = array_diff($quoteSku, $orderSku);
        $diffData = [];
        $newItems = [];

        if (!empty($diffSku)) {
            foreach ($diffSku as $sku) {
                if (in_array($sku, $quoteSku) && !in_array($sku, $orderSku)) {
                    $diffData[$sku] = 'new';
                    $newItems[$sku] = $quoteItems[$sku];
                }
            }
        }

        if (!empty(array_diff($orderSku, $quoteSku))) {
            foreach (array_diff($orderSku, $quoteSku) as $sku) {
                if (in_array($sku, $orderSku) && !in_array($sku, $quoteSku)) {
                    $removeItem[$sku] = $orderItems[$sku];
                }
            }
        }

        $invoices    = [];
        $creditmemos = [];
        $shipments   = [];

        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoices[] = $invoice;
        }

        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $creditmemos[] = $creditmemo;
        }

        foreach ($order->getShipmentsCollection() as $ship) {
            $shipments[] = $ship;
        }

        if (empty($order->getAllItems())) {
            $order->setItems($this->quoteManagement->getResolveItems($quote)); //set new items for order item
        } else {
            if (!empty($this->quoteManagement->getResolveItems($quote))) {
                $subtotalRemove        = 0;
                $existItems            = [];
                $existItemSku          = [];
                $existItemCaB          = [];
                $quoteItemIds          = [];
                $childItemIds          = [];
                $invoiceItemFactory    = $this->invoiceItemFactory->create();
                $creditmemoItemFactory = $this->creditmemoItemFactory->create();
                $shipmentItemFactory   = $this->shipmentItemFactory->create();

                foreach ($this->quoteManagement->getResolveItems($quote) as $quoteItem) {
                    $product                            = $this->_productRepository->getById($quoteItem->getProductId());
                    $quoteItemIds[$quoteItem->getSku()] = $quoteItem->getId();
                    if ($product->getTypeId() === Configurable::TYPE_CODE || $product->getTypeId() === Type::TYPE_BUNDLE) {
                        if ($product->getTypeId() === Configurable::TYPE_CODE) {
                            $childrens = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childrens as $child) {
                                $childItemIds[$child->getID()] = $quoteItem->getProductId();
                            }
                        }
                        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
                            $requiredChildrenIds = $product->getTypeInstance()->getChildrenIds(
                                $product->getId(),
                                false
                            );
                            foreach ($requiredChildrenIds as $requiredChildrenId) {
                                foreach ($requiredChildrenId as $productId) {
                                    $childItemIds[$productId] = $quoteItem->getProductId();
                                }
                            }
                        }

                        $existItemCaB[$quoteItem->getProductId()] = $quoteItem;
                    }
                    if (isset($orderItems[$quoteItem->getSku()])) {
                        $existItems[$quoteItem->getSku()] = $quoteItem;
                        $existItemSku[]                   = $quoteItem->getSku();
                    }
                }

                $itemCollection = $this->itemCollectionFactory->create();
                $itemCollection->addFieldToFilter('order_id', $order->getId());
                $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];
                foreach ($order->getAllItems() as $orderItem) {
                    if (isset($existItems[$orderItem->getSku()])) {
                        $quoteItem = $existItems[$orderItem->getSku()];
                        if ($orderItem->getProductType() === Configurable::TYPE_CODE || $orderItem->getProductType() === Type::TYPE_BUNDLE) {
                            if (isset($existItemCaB[$orderItem->getProductId()])) {
                                $quoteItem = $existItemCaB[$orderItem->getProductId()];
                            }
                        }
                        foreach ($orderItem->getData() as $key => $dataItem) {
                            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
                            $logger = new \Zend_Log();
                            $logger->addWriter($writer);
                            $logger->info(json_encode($key));
                            if (!in_array($key, ['item_id','quote_item_id','parent_item','parent_item_id'])) {
                                $logger->info(json_encode($quoteItem->getData($key)));
                                if ($updateAfterEditingConfig === 0) {
                                    $qty_invoiced = $orderItem->getQtyInvoiced();
                                    $qty_shipped  = $orderItem->getQtyShipped();
                                    $qty_refunded = $orderItem->getQtyRefunded();
//                                    $orderItem->setData($key, $quoteItem->getData($key));
                                    $orderItem->setQtyInvoiced($qty_invoiced);
                                    $orderItem->setQtyShipped($qty_shipped);
                                    $orderItem->setQtyRefunded($qty_refunded);
                                } else {
//                                    $orderItem->setData($key, $quoteItem->getData($key));
                                }
                            }
                        }

                        if (!$orderItem->getParentItemId() && isset($childItemIds[$orderItem->getProductId()])) {
                            $itemCollection->addFieldToFilter('product_id', $childItemIds[$orderItem->getProductId()]);
                            foreach ($itemCollection as $value) {
                                $parentId = $value->getItemId();
                            }
                            if (isset($parentId)) {
                                $orderItem->setParentItemId($parentId);
                            }
                        }
                        $orderItem->save();
                    }

                    if (!in_array($orderItem->getSku(), $existItemSku)) {
                        foreach ($invoices as $invoice) {
                            foreach ($invoice->getItems() as $item) {
                                if ($item->getEntityId()) {
                                    $invoiceItemFactory->load($item->getEntityId());
                                    if ($item->getSku() === $orderItem->getSku()) {
                                        $invoiceItemFactory->setOrderItemData(json_encode($orderItem->getData()));
                                        $invoiceItemFactory->save();
                                    }
                                }
                            }
                        }

                        foreach ($creditmemos as $creditmemo) {
                            foreach ($creditmemo->getItems() as $item) {
                                if ($item->getEntityId()) {
                                    $creditmemoItemFactory->load($item->getEntityId());
                                    if ($item->getSku() === $orderItem->getSku()) {
                                        $creditmemoItemFactory->setOrderItemData(json_encode($orderItem->getData()));
                                        $creditmemoItemFactory->save();
                                    }
                                }
                            }
                        }

                        foreach ($shipments as $shipment) {
                            foreach ($shipment->getItems() as $item) {
                                if ($item->getEntityId()) {
                                    $shipmentItemFactory->load($item->getEntityId());
                                    if ($item->getSku() === $orderItem->getSku()) {
                                        $shipmentItemFactory->setOrderItemData(json_encode($orderItem->getData()));
                                        $shipmentItemFactory->save();
                                    }
                                }
                            }
                        }
                        $subtotalRemove += $orderItem->getRowTotal();
                        $orderItem->delete();
                    }
                }

                if (!empty($newItems)) {
                    foreach ($newItems as $sku => $item) {
                        if ($diffData[$sku] === 'new') {
                            $order->addItem($item);
                        }
                    }
                }
            }
        }

        foreach ($order->getItems() as $item) {
            $weight += $this->weightCalculate($item->getProductId(), $item->getQtyOrdered());
        }

        foreach ($order->getAllItems() as $item) {
            if (array_key_exists($item->getSku(), $removeItem)) {
                unset($removeItem[$item->getSku()]);
            }
        }

        if (!empty($removeItem)) {
            foreach ($removeItem as $item) {
                $weight -= $this->weightCalculate($item->getProductId(), $item->getQtyOrdered());
                if ($item->getParentItem()) {
                    $item = $item->getParentItem();
                }
                $backToSalableQty = $backToStockQty = (float) $item->getQtyOrdered();
                if ((float) $item->getQtyShipped() > 0) {
                    $backToSalableQty = 0;
                    if ((float) $item->getQtyOrdered() > (float) $item->getQtyShipped()) {
                        $backToSalableQty = (float) $item->getQtyOrdered() - (float) $item->getQtyShipped();
                        $backToStockQty   = (float) $item->getQtyShipped();
                    }
                    if ($this->_helperData->isReturnItemToStock()) {
                        $stockItem = $this->stockRegistry->getStockItemBySku($item->getSku());
                        $stockItem->setQty($stockItem->getQty() + $backToStockQty);
                        $this->stockRegistry->updateStockItemBySku($item->getSku(), $stockItem);
                    }
                }

                if ($backToSalableQty > 0) {
                    $this->resetSalableQuantity($item, $backToSalableQty);
                }
            }
        }

        $order->setWeight($weight);
        $newSubtotal        = $this->getSubtotalByItems($order) - $subtotalRemove;
        $newSubtotalInclTax = $itemsData['tax_amount'] + $newSubtotal;
        $newGrandTotal      = $newSubtotal + $shipAmount + $newTaxAmount - $newDiscountAmount;

        return [
            'tax_amount'        => $newTaxAmount,
            'subtotal'          => $newSubtotal,
            'discount_amount'   => $newDiscountAmount,
            'grand_total'       => $newGrandTotal,
            'subtotal_incl_tax' => $newSubtotalInclTax
        ];
    }

    /**
     * @param $coupon
     *
     * @return string|null
     * @throws Exception
     */
    public function checkCouponCodeFreeShipping($coupon)
    {
        $ruleId=$this->coupon->loadByCode($coupon)->getRuleId();
        $getRule = $this->ruleRepostitory->getById($ruleId);
        return $getRule->getSimpleFreeShipping();
    }

    /**
     * @param Quote $quote
     *
     * @return bool
     */
    public function isQuoteItemOutStock($quote)
    {
        foreach ($this->quoteManagement->getResolveItems($quote) as $key => $item) {
            if (!$item->getProduct()->getQuantityAndStockStatus()['is_in_stock']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total discount amount all items
     *
     * @param Order $order
     *
     * @return float|null
     */
    public function getItemsDiscountAmount($order)
    {
        $discount = 0;

        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                continue;
            }
            $discount += $item->getBaseDiscountAmount();
        }

        return $discount;
    }

    /**
     * Get total tax amount all items
     *
     * @param Order $order
     *
     * @return float
     */
    public function getItemsTaxAmount($order)
    {
        $tax = 0;

        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                continue;
            }
            $tax += $item->getBaseTaxAmount();
        }

        return $tax;
    }

    /**
     * @param Order $order
     *
     * @return float
     */
    public function getSubtotalByItems($order)
    {
        $subtotal = 0;

        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                continue;
            }

            $subtotal += $item->getBaseRowTotal();
        }

        return $subtotal;
    }

    /**
     * @return bool
     */
    public function isEnableInventorySales()
    {
        return $this->_helperData->isEnableInventorySales();
    }

    /**
     * @param $productId
     * @param $qty
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function weightCalculate($productId, $qty)
    {
        $weight = 0;
        $product = $this->_productRepository->getById($productId);
        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
            $weight = $qty*$product->getWeight();
        }
        return $weight;
    }
}
