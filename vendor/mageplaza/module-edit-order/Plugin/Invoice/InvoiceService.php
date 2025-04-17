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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Plugin\Invoice;

use Closure;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Convert\Order as OrderConverter;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CreateByOrder
 * @package Mageplaza\EditOrder\Plugin\Invoice
 */
class InvoiceService
{
    /**
     * Order convert object.
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $convertor;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize
     * @deprecated 101.0.0
     */
    protected $unserialize;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderConverter
     */
    protected $orderConverter;

    /**
     * @param \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param JsonSerializer|null $serializer
     * @param FormatInterface|null $localeFormat
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param OrderConverter $orderConverter
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Tax\Model\Config $taxConfig,
        JsonSerializer $serializer = null,
        FormatInterface $localeFormat = null,
        RequestInterface $request,
        HelperData $helperData,
        OrderConverter $orderConverter,
        OrderFactory $orderFactory
    ) {
        $this->_request       = $request;
        $this->_helperData    = $helperData;
        $this->orderConverter = $orderConverter;
        $this->orderFactory   = $orderFactory;
        $this->taxConfig      = $taxConfig;
        $this->convertor      = $convertOrderFactory->create();
        $this->serializer     = $serializer ?: ObjectManager::getInstance()->get(JsonSerializer::class);
        $this->localeFormat   = $localeFormat ?: ObjectManager::getInstance()->get(FormatInterface::class);
    }

    /**
     * @param \Magento\Sales\Model\Service\InvoiceService $object
     * @param Closure $process
     * @param $order
     * @param array $orderItemsQtyToInvoice
     * @return Invoice
     * @throws LocalizedException
     */
    public function aroundPrepareInvoice(\Magento\Sales\Model\Service\InvoiceService $object, Closure $process, $order, $orderItemsQtyToInvoice = [])
    {
        $totalQty = 0;
        $invoice = $this->orderConverter->toInvoice($order);
        $preparedItemsQty = $this->prepareItemsQty($order, $orderItemsQtyToInvoice);

        $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];

        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canInvoiceItem($orderItem, $preparedItemsQty)) {
                continue;
            }

            if (isset($preparedItemsQty[$orderItem->getId()])) {
                $qty = $preparedItemsQty[$orderItem->getId()];
            } elseif ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (empty($orderItemsQtyToInvoice)) {
                $qty = $orderItem->getQtyToInvoice();
            } else {
                $qty = 0;
            }

            $invoiceItem = $this->orderConverter->itemToInvoiceItem($orderItem);
            $this->setInvoiceItemQuantity($invoiceItem, (float) $qty);
            $invoice->addItem($invoiceItem);
            $totalQty += $qty;
        }

        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();

        if ((int)$order->getMpIsEditOrder() === 1 &&
            $updateAfterEditingConfig === 0 &&
            $this->_helperData->isEnabled()) {
            $baseSubTotal      = 0;
            $baseTaxTotal      = 0;
            $baseDiscountTotal = 0;
            $subTotal          = 0;
            $taxTotal          = 0;
            $discountTotal     = 0;

            foreach ($invoice->getAllItems() as $item) {
                $baseSubTotal      += $item->getBasePrice() * $item->getQty();
                $baseTaxTotal      += $item->getBaseTaxAmount();
                $baseDiscountTotal += $item->getBaseDiscountAmount();
                $subTotal          += $item->getPrice() * $item->getQty();
                $taxTotal          += $item->getTaxAmount();
                $discountTotal     += $item->getDiscountAmount();
            }

            $baseGrandTotal = $baseSubTotal - $baseDiscountTotal + $baseTaxTotal + $invoice->getBaseShippingAmount();
            $grandTotal = $subTotal - $discountTotal + $taxTotal + $invoice->getShippingAmount();

            $invoice->setSubtotal($subTotal);
            $invoice->setBaseSubtotal($baseSubTotal);
            $invoice->setTaxAmount($taxTotal);
            $invoice->setBaseTaxAmount($baseTaxTotal);
            $invoice->setDiscountAmount(0-$discountTotal);
            $invoice->setBaseDiscountAmount(0-$baseDiscountTotal);
            $invoice->setGrandTotal($grandTotal);
            $invoice->setBaseGrandTotal($baseGrandTotal);
        }

        $order->getInvoiceCollection()->addItem($invoice);

        return $invoice;
    }

    /**
     * Prepare qty to invoice for parent and child products if theirs qty is not specified in initial request.
     *
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    private function prepareItemsQty(
        Order $order,
        array $orderItemsQtyToInvoice
    ): array {
        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderItemsQtyToInvoice[$orderItem->getId()])) {
                if ($orderItem->isDummy() && $orderItem->getHasChildren()) {
                    $orderItemsQtyToInvoice = $this->setChildItemsQtyToInvoice($orderItem, $orderItemsQtyToInvoice);
                }
            } else {
                if (isset($orderItemsQtyToInvoice[$orderItem->getParentItemId()])) {
                    $orderItemsQtyToInvoice[$orderItem->getId()] =
                        $orderItemsQtyToInvoice[$orderItem->getParentItemId()];
                }
            }
        }

        return $orderItemsQtyToInvoice;
    }

    /**
     * Sets qty to invoice for children order items, if not set.
     *
     * @param OrderItemInterface $parentOrderItem
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    private function setChildItemsQtyToInvoice(
        OrderItemInterface $parentOrderItem,
        array $orderItemsQtyToInvoice
    ): array {
        /** @var OrderItemInterface $childOrderItem */
        foreach ($parentOrderItem->getChildrenItems() as $childOrderItem) {
            if (!isset($orderItemsQtyToInvoice[$childOrderItem->getItemId()])) {
                $productOptions = $childOrderItem->getProductOptions();

                if (isset($productOptions['bundle_selection_attributes'])) {
                    $bundleSelectionAttributes = $this->serializer
                        ->unserialize($productOptions['bundle_selection_attributes']);
                    $orderItemsQtyToInvoice[$childOrderItem->getItemId()] =
                        $bundleSelectionAttributes['qty'] * $orderItemsQtyToInvoice[$parentOrderItem->getItemId()];
                }
            }
        }

        return $orderItemsQtyToInvoice;
    }

    /**
     * Check if order item can be invoiced.
     *
     * @param OrderItemInterface $item
     * @param array $qtys
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canInvoiceItem(OrderItemInterface $item, array $qtys): bool
    {
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }

    /**
     * @param $item
     * @param float $qty
     * @return $this
     * @throws LocalizedException
     */
    private function setInvoiceItemQuantity($item, float $qty)
    {
        $qty = ($item->getOrderItem()->getIsQtyDecimal()) ? (double) $qty : (int) $qty;
        $qty = $qty > 0 ? $qty : 0;

        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $item->getOrderItem()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty > $qtyToInvoice && !$item->getOrderItem()->isDummy()) {
            throw new LocalizedException(
                __('We found an invalid quantity to invoice item "%1".', $item->getName())
            );
        }

        $item->setQty($qty);

        return $this;
    }
}
