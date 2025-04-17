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
 * @category  Mageplaza
 * @package   Mageplaza_EditOrder
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Model\Quote;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku as GetSalableQuantityDataBySkuAlias;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class IsSalableWithReservationsCondition
 * @package Mageplaza\EditOrder\Model\Quote
 */
class IsSalableWithReservationsCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    protected $getStockItemData;

    /**
     * @var GetReservationsQuantityInterface
     */
    protected $getReservationsQuantity;

    /**
     * @var GetStockItemConfigurationInterface
     */
    protected $getStockItemConfiguration;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    protected $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    protected $productSalableResultFactory;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var GetSalableQuantityDataBySkuAlias
     */
    protected $_getSalableQuantityDataBySku;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param GetSalableQuantityDataBySkuAlias $getSalableQuantityDataBySku
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        GetSalableQuantityDataBySkuAlias $getSalableQuantityDataBySku,
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        RequestInterface $request,
        HelperData $helperData,
        StockRegistryInterface $stockRegistry
    ) {
        $this->_request                      = $request;
        $this->_getSalableQuantityDataBySku  = $getSalableQuantityDataBySku;
        $this->getStockItemData              = $getStockItemData;
        $this->getReservationsQuantity       = $getReservationsQuantity;
        $this->getStockItemConfiguration     = $getStockItemConfiguration;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory   = $productSalableResultFactory;
        $this->_helperData                   = $helperData;
        $this->stockRegistry                 = $stockRegistry;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        if (null === $stockItemData) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code'    => 'is_salable_with_reservations-no_data',
                    'message' => __('The requested sku is not assigned to given stock')
                ])
            ];

            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $qtyWithReservation     = $stockItemData[GetStockItemDataInterface::QUANTITY] + $this->getReservationsQuantity->execute($sku, $stockId);
        $qtyLeftInStock         = $qtyWithReservation - $stockItemConfiguration->getMinQty();
        $isInStock              = bccomp((string)$qtyLeftInStock, (string)$requestedQty, 4) >= 0;

        // check SableQty of Product and Qty Ordered of Item.
        $request = explode('_', $this->_request->getFullActionName());

        if (in_array("mpeditorder", $request)) {
            if ($this->_request->getFullActionName() === 'mpeditorder_items_form') {
                $isInStock = true;
            } else {
                $qty    = $this->_getSalableQuantityDataBySku->execute($sku);
                $params = $this->_request->getParams();

                if (isset($qty[0]['qty']) && isset($params['item'])) {
                    $qtyOrdered   = 0;
                    $qtyReOrdered = 0;

                    foreach ($params['item'] as $key => $item) {
                        if (isset($item['sku']) && $item['sku'] === $sku && isset($item['qty_ordered']) && isset($item['qty'])) {
                            $qtyOrdered   = $item['qty_ordered'];
                            $qtyReOrdered = $item['qty'];
                            break;
                        }
                    }
                    $qtyCanReOrdered = $qtyOrdered + $qty[0]['qty'];
                    $isInStock       = $qtyCanReOrdered >= $qtyReOrdered;
                }
            }
        }// end check

        if ($this->_helperData->getConfigValue('cataloginventory/item_options/backorders') != 0) {
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $isInStock = (bool)$stockItem->getIsInStock();
        }

        $isEnoughQty = ((bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE] || $requestedQty <= 0) && $isInStock;

        if (!$isEnoughQty) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code'    => 'is_salable_with_reservations-not_enough_qty',
                    'message' => __('The requested qty is not available')
                ])
            ];

            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
