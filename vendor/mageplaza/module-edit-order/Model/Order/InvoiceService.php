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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Sales\Model\Order\InvoiceNotifier;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;

/**
 * Class InvoiceService
 * @package Mageplaza\EditOrder\Model\Order
 */
class InvoiceService
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * @var InvoiceCommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var InvoiceNotifier
     */
    protected $invoiceNotifier;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ConvertOrder
     */
    protected $orderConverter;

    /**
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * @param InvoiceRepositoryInterface $repository
     * @param InvoiceCommentRepositoryInterface $commentRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param InvoiceNotifier $notifier
     * @param OrderRepositoryInterface $orderRepository
     * @param ConvertOrder $orderConverter
     * @param JsonSerializer $serializer
     */
    public function __construct(
        InvoiceRepositoryInterface $repository,
        InvoiceCommentRepositoryInterface $commentRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        InvoiceNotifier $notifier,
        OrderRepositoryInterface $orderRepository,
        ConvertOrder $orderConverter,
        JsonSerializer $serializer
    ) {
        $this->repository        = $repository;
        $this->commentRepository = $commentRepository;
        $this->criteriaBuilder   = $criteriaBuilder;
        $this->filterBuilder     = $filterBuilder;
        $this->invoiceNotifier   = $notifier;
        $this->orderRepository   = $orderRepository;
        $this->orderConverter    = $orderConverter;
        $this->serializer        = $serializer;
    }

    /**
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @param array $invoiceData
     * @return InvoiceInterface
     * @throws LocalizedException
     */
    public function prepareInvoice(
        Order $order,
        array $orderItemsQtyToInvoice = [],
        array $invoiceData = []
    ): InvoiceInterface {
        $totalQty         = 0;
        $invoice          = $this->orderConverter->toInvoice($order);
        $preparedItemsQty = $this->prepareItemsQty($order, $orderItemsQtyToInvoice);

        foreach ($order->getAllItems() as $orderItem) {
            if (!isset($invoiceData[$orderItem->getProductId()])) {
                continue;
            }

            if ((int) $invoiceData[$orderItem->getProductId()] === 0) {
                continue;
            }

            if (isset($invoiceData[$orderItem->getProductId()])) {
                $qty = $invoiceData[$orderItem->getProductId()];
            } elseif (isset($preparedItemsQty[$orderItem->getId()])) {
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
    public function prepareItemsQty(
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
    public function setChildItemsQtyToInvoice(
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
     * @param $item
     * @param float $qty
     * @return $this
     * @throws LocalizedException
     */
    public function setInvoiceItemQuantity($item, float $qty)
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
