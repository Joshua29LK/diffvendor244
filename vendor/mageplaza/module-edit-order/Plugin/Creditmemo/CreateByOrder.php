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

namespace Mageplaza\EditOrder\Plugin\Creditmemo;

use Closure;
use Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CreateByOrder
 * @package Mageplaza\EditOrder\Plugin\Creditmemo
 */
class CreateByOrder
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
     * @param \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param JsonSerializer|null $serializer
     * @param FormatInterface|null $localeFormat
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Tax\Model\Config $taxConfig,
        JsonSerializer $serializer = null,
        FormatInterface $localeFormat = null,
        RequestInterface $request,
        HelperData $helperData,
        OrderFactory $orderFactory
    ) {
        $this->_request     = $request;
        $this->_helperData  = $helperData;
        $this->orderFactory = $orderFactory;
        $this->taxConfig    = $taxConfig;
        $this->convertor    = $convertOrderFactory->create();
        $this->serializer   = $serializer ?: ObjectManager::getInstance()->get(JsonSerializer::class);
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(FormatInterface::class);
    }

    /**
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $object
     * @param Closure $process
     * @return Creditmemo
     */
    public function aroundCreateByOrder(\Magento\Sales\Model\Order\CreditmemoFactory $object, Closure $process, $order, $data = [])
    {
        $issetCreditmemos         = false;
        $updateAfterEditingConfig = (int) $this->_helperData->getUpdateAfterEditing()[0];

        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            if ((string)$creditmemo->getState() !== '3') {
                $issetCreditmemos   = true;
            }
        }

        $creditmemo     = $this->convertor->toCreditmemo($order);
        $qtyList        = isset($data['qtys']) ? $data['qtys'] : [];
        $totalQty       = 0;
        $baseGrandTotal = 0;
        $baseSubTotal   = 0;
        $baseTaxAmount  = 0;
        $grandTotal     = 0;
        $subTotal       = 0;
        $taxAmount      = 0;
        $qtyOrder       = [];

        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canRefundItem($orderItem, $qtyList) || $orderItem->getProductType() === 'bundle') {
                continue;
            }

            $item = $this->convertor->itemToCreditmemoItem($orderItem);
            $qty  = $this->getQtyToRefund($orderItem, $qtyList);

            if (($orderItem->getQtyOrdered() < $orderItem->getQtyInvoiced()) && $updateAfterEditingConfig === 0) {
                if ((int) $orderItem->getQtyRefunded() === 0) {
                    $qty = $orderItem->getQtyOrdered();
                } elseif ($orderItem->getQtyOrdered() > $orderItem->getQtyRefunded()) {
                    $qty = (int) $orderItem->getQtyOrdered() - (int) $orderItem->getQtyRefunded();
                } else {
                    continue;
                }
            }

            if ((int) $qty === 0) {
                continue;
            }

            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
            $qtyOrder[$orderItem->getProductId()] = $orderItem->getQtyOrdered();
            $baseGrandTotal += $orderItem->getBaseRowTotal();
            $baseSubTotal   += $orderItem->getBasePrice() * $item->getQty();
            $grandTotal     += $orderItem->getRowTotal();
            $subTotal       += $orderItem->getPrice() * $item->getQty();
        }

        $creditmemo->setTotalQty($totalQty);
        $this->initData($creditmemo, $data);
        $creditmemo->collectTotals();

        if ((int)$order->getMpIsEditOrder() === 1 && $this->_helperData->isEnabled()) {
            $creditItems = $creditmemo->getItems();
            $newItems = [];

            foreach ($creditItems as $item) {
                $item->setBaseRowTotal($item->getQty() * $item->getBasePrice());
                $item->setRowTotal($item->getQty() * $item->getPrice());

                if (isset($qtyOrder[$item->getProductId()])) {
                    $baseTax = $item->getBaseTaxAmount()/$qtyOrder[$item->getProductId()];
                    $tax     = $item->getTaxAmount()/$qtyOrder[$item->getProductId()];
                    $baseTaxAmount += $baseTax;
                    $taxAmount     += $tax;
                    $item->setBaseTaxAmount($baseTax);
                    $item->setTaxAmount($tax);
                } else {
                    $baseTaxAmount += $item->getBaseTaxAmount();
                    $taxAmount     += $item->getTaxAmount();
                }

                $newItems[] = $item;
            }

            $creditmemo->setItems($newItems);

            if (!$issetCreditmemos) {
                $creditmemo->setBaseShippingAmount($order->getBaseShippingAmount());
                $creditmemo->setShippingAmount($order->getShippingAmount());
            }
            $creditmemo->setBaseTaxAmount($baseTaxAmount);
            $creditmemo->setTaxAmount($taxAmount);
            $creditmemo->setBaseSubTotal($baseSubTotal);
            $creditmemo->setSubTotal($subTotal);

            $baseGrandTotal = $baseSubTotal + $creditmemo->getBaseDiscountAmount() + $baseTaxAmount + $creditmemo->getBaseShippingAmount()
                                    + $creditmemo->getBaseAdjustmentPositive() + $creditmemo->getBaseAdjustmentNegative();
            $grandTotal = $subTotal + $creditmemo->getDiscountAmount() + $taxAmount + $creditmemo->getShippingAmount()
                                    + $creditmemo->getAdjustmentPositive() + $creditmemo->getAdjustmentNegative();

            $creditmemo->setGrandTotal($grandTotal);
            $creditmemo->setBaseGrandTotal($baseGrandTotal);
        }

        return $creditmemo;
    }

    /**
     * Check if order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = [])
    {
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys) || (count(array_unique($qtys)) === 1 && (int)end($qtys) === 0)) {
                        if ($this->canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
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
                    return $this->canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
            return false;
        } else {
            return $this->canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function canRefundNoDummyItem($item, $invoiceQtysRefundLimits = [])
    {
        if ($item->getQtyToRefund() < 0) {
            return false;
        }
        if (isset($invoiceQtysRefundLimits[$item->getId()])) {
            return $invoiceQtysRefundLimits[$item->getId()] > 0;
        }
        return true;
    }

    /**
     * Initialize creditmemo state based on requested parameters
     *
     * @param Creditmemo $creditmemo
     * @param array $data
     * @return void
     */
    protected function initData($creditmemo, $data)
    {
        if (isset($data['shipping_amount'])) {
            $shippingAmount = $this->parseNumber($data['shipping_amount']);
            $creditmemo->setBaseShippingAmount($shippingAmount);
            $creditmemo->setBaseShippingInclTax($shippingAmount);
        }
        if (isset($data['adjustment_positive'])) {
            $adjustmentPositiveAmount = $this->parseAdjustmentAmount($data['adjustment_positive']);
            $creditmemo->setAdjustmentPositive($adjustmentPositiveAmount);
        }
        if (isset($data['adjustment_negative'])) {
            $adjustmentNegativeAmount = $this->parseAdjustmentAmount($data['adjustment_negative']);
            $creditmemo->setAdjustmentNegative($adjustmentNegativeAmount);
        }
    }

    /**
     * Calculate product options.
     *
     * @param Item $orderItem
     * @param int $parentQty
     * @return int
     */
    private function calculateProductOptions(Item $orderItem, int $parentQty): int
    {
        $qty = $parentQty;
        $productOptions = $orderItem->getProductOptions();
        if (isset($productOptions['bundle_selection_attributes'])) {
            $bundleSelectionAttributes = $this->serializer->unserialize(
                $productOptions['bundle_selection_attributes']
            );
            if ($bundleSelectionAttributes) {
                $qty = $bundleSelectionAttributes['qty'] * $parentQty;
            }
        }
        return $qty;
    }

    /**
     * Gets quantity of items to refund based on order item.
     *
     * @param Item $orderItem
     * @param array $qtyList
     * @param array $refundLimits
     * @return float
     */
    private function getQtyToRefund(Item $orderItem, array $qtyList, array $refundLimits = []): float
    {
        $qty = 0;
        if ($orderItem->isDummy()) {
            if (isset($qtyList[$orderItem->getParentItemId()])) {
                $parentQty = $qtyList[$orderItem->getParentItemId()];
            } elseif ($orderItem->getProductType() === BundlePrice::PRODUCT_TYPE) {
                $parentQty = $orderItem->getQtyInvoiced();
            } else {
                $parentQty = $orderItem->getParentItem() ? $orderItem->getParentItem()->getQtyToRefund() : 1;
            }
            $qty = $this->calculateProductOptions($orderItem, $parentQty);
        } else {
            if (isset($qtyList[$orderItem->getId()])) {
                $qty = $qtyList[$orderItem->getId()];
            } elseif (!count($qtyList)) {
                $qty = $orderItem->getQtyToRefund();
            } else {
                return (float)$qty;
            }

            if (isset($refundLimits[$orderItem->getId()])) {
                $qty = min($qty, $refundLimits[$orderItem->getId()]);
            }
        }

        return (float)$qty;
    }

    /**
     * Parse adjustment amount value to number
     *
     * @param string|null $amount
     *
     * @return float|null
     */
    private function parseAdjustmentAmount($amount)
    {
        $amount = trim($amount);
        $percentAmount = substr($amount, -1) == '%';
        $amount = $this->parseNumber($amount);

        return $percentAmount ? $amount . '%' : $amount;
    }

    /**
     * Parse value to number
     *
     * @param string|null $value
     *
     * @return float|null
     */
    private function parseNumber($value)
    {
        return $this->localeFormat->getNumber($value);
    }
}
