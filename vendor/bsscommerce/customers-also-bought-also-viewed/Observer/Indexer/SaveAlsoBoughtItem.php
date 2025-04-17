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
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Observer\Indexer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Serialize\Serializer\Json;

class SaveAlsoBoughtItem implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * SaveAlsoBoughtItem constructor.
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        Json $serializer
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderProductIds = [];
        $listItems = [];
        foreach ($order->getAllItems() as $item) {
            $productType = $item->getProductType();
            $productId = $item->getProductId();
            $storeId = $item->getStoreId();
            $qtyOrdered = $item->getQtyOrdered();
            if ($productType == 'grouped') {
                $productOptions = $item->getProductOptions();
                $productId = $productOptions['super_product_config']['product_id'];
            } elseif ($productType == 'simple' && $item->getParentItem()) {
                continue;
            }
            if (isset($listItems[$productId])) {
                $listItems[$productId]['qty_ordered'] = (int)$qtyOrdered + $listItems[$productId]['qty_ordered'];
            } else {
                $listItems[$productId]['store_id'] = $storeId;
                $listItems[$productId]['qty_ordered'] = $qtyOrdered;
            }
            $orderProductIds[$productId] = $productId;
        }
        $data = $this->serializer->serialize(['list_items' => $listItems, 'order_productids' => $orderProductIds]);
        $this->indexerRegistry->get('bss_cabav_also_bought_indexer')->reindexRow($data);
    }
}
