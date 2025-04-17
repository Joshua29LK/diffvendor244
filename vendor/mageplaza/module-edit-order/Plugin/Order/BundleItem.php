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

namespace Mageplaza\EditOrder\Plugin\Order;

use Closure;
use Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class BundleItem
 * @package Mageplaza\EditOrder\Plugin\Order
 */
class BundleItem extends AbstractItems
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var Shipment
     */
    protected $_shipment;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Serializer
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param HelperData $helperData
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param Shipment $shipment
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        HelperData $helperData,
        ItemCollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        Shipment $shipment,
        Json $serializer = null,
        array $data = []
    ) {
        $this->helperData            = $helperData;
        $this->_request              = $request;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_productRepository    = $productRepository;
        $this->_shipment             = $shipment;
        $this->serializer            = $serializer ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * @return array|Order|Shipment|mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        if ($this->_request->getParam('order_id')) {
            return $this->helperData->getOrderById($this->_request->getParam('order_id'));
        }

        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }
        if ($this->_coreRegistry->registry('order')) {
            return $this->_coreRegistry->registry('order');
        }
        if ($this->getInvoice()) {
            return $this->getInvoice()->getOrder();
        }
        if ($this->getCreditmemo()) {
            return $this->getCreditmemo()->getOrder();
        }
        if ($this->getItem() !== null && $this->getItem()->getOrder()) {
            return $this->getItem()->getOrder();
        }

        if ($this->_request->getParam('shipment_id')) {
            return $this->getShipmentById($this->_request->getParam('shipment_id'))->getOrder();
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    /**
     * @param Renderer $object
     * @param Closure $process
     * @param $item
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetChildren(Renderer $object, Closure $process, $item)
    {
        $order = $this->getOrder();
        if ($this->helperData->isEnabled() && (int)$order->getMpIsEditOrder() === 1) {
            $itemsArray = [];
            $items = null;
            if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
                $items = $item->getInvoice()->getAllItems();
            } elseif ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
                $items = $item->getShipment()->getAllItems();
            } elseif ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
                $items = $item->getCreditmemo()->getAllItems();
            }

            $itemParentId  = $this->getNewParentId($order, $item->getOrderItem()->getId(), $item->getOrderItem()->getProductId());

            if ($items) {
                $childItemIds = $this->getChildProductIds($order);
                $itemsArray[$itemParentId][$item->getOrderItemId()] = $item;
                foreach ($items as $value) {
                    $parentItem = $value->getOrderItem()->getParentItem();
                    if ($parentItem) {
                        if (empty(get_class_methods($parentItem)) && $value->getOrderItemData() != null) {
                            $orderItem = (array)json_decode($value->getOrderItemData());
                            if (isset($childItemIds[$orderItem['product_id']])) {
                                $itemCollection = $this->itemCollectionFactory->create();
                                $itemCollection->addFieldToFilter('order_id', $order->getId());
                                $itemCollection->addFieldToFilter('product_id', $childItemIds[$orderItem['product_id']]);
                                foreach ($itemCollection as $vl) {
                                    $parentId = $vl->getItemId();
                                }
                                if (isset($parentId)) {
                                    $itemsArray[$parentId][$orderItem['item_id']] = $value;
                                }
                            }
                        } else {
                            $itemsArray[$parentItem->getId()][$value->getOrderItemId()] = $value;
                        }
                    } else {
                        $itemsArray[$value->getOrderItem()->getId()][$value->getOrderItemId()] = $value;
                    }
                }
            }

            if (isset($itemsArray[$itemParentId])) {
                return $itemsArray[$itemParentId];
            }
            return null;
        }

        return $process($item);
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildProductIds($order)
    {
        $childItemIds = [];
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $order->getId());
        foreach ($itemCollection as $orderCl) {
            if ($orderCl->getProductType() === 'bundle' || $orderCl->getProductType() === 'configurable') {
                $product = $this->_productRepository->getById($orderCl->getProductId());
                if ($product->getTypeId() === 'configurable' || $product->getTypeId() === 'bundle') {
                    if ($product->getTypeId() === 'configurable') {
                        $childrens = $product->getTypeInstance()->getUsedProducts($product);
                        foreach ($childrens as $child) {
                            $childItemIds[$child->getID()] = $orderCl->getProductId();
                        }
                    }
                    if ($product->getTypeId() === 'bundle') {
                        $requiredChildrenIds = $product->getTypeInstance()->getChildrenIds($product->getId(), false);
                        foreach ($requiredChildrenIds as $requiredChildrenId) {
                            foreach ($requiredChildrenId as $productId) {
                                $childItemIds[$productId] = $orderCl->getProductId();
                            }
                        }
                    }
                }
            }
        }

        return $childItemIds;
    }

    /**
     * @param $order
     * @param $parentId
     * @param $productId
     * @return mixed|string|null
     */
    public function getNewParentId($order, $parentId, $productId)
    {
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $order->getId());
        $itemCollection->addFieldToFilter('item_id', $parentId);

        if (count($itemCollection) === 0) {
            if ($this->getParentId($order, $productId)) {
                return $this->getParentId($order, $productId);
            }
        }

        return $parentId;
    }

    /**
     * @param $order
     * @param $productId
     * @return string
     */
    public function getParentId($order, $productId)
    {
        $parentId       = null;
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $order->getId());
        $itemCollection->addFieldToFilter('product_id', $productId);

        foreach ($itemCollection as $item) {
            if ($item->getProductType() === 'bundle' || $item->getProductType() === 'configurable') {
                $parentId = $item->getItemId();
            }
        }

        return $parentId;
    }

    /**
     * @param Renderer $object
     * @param Closure $process
     * @param $item
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundIsShipmentSeparately(Renderer $object, Closure $process, $item = null)
    {
        $order = $this->getOrder();
        if ($this->helperData->isEnabled() && (int)$order->getMpIsEditOrder() === 1) {
            if ($item) {
                $itemData = null;
                if ($item->getOrderItem()) {
                    $itemData = $item->getOrderItemData();
                    $item     = $item->getOrderItem();
                }

                $parentItem = $item->getParentItem();
                if ($parentItem) {
                    if (empty(get_class_methods($parentItem)) && $itemData != null) {
                        $orderItem = (array) json_decode($itemData);
                        $options   = (array) $orderItem['product_options'];
                    } else {
                        $options   = $parentItem->getProductOptions();
                    }
                    if ($options) {
                        return (isset($options['shipment_type'])
                            && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                    }
                } else {
                    $options = $item->getProductOptions();
                    if ($options) {
                        return !(isset($options['shipment_type'])
                            && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                    }
                }
            }

            $options = $object->getOrderItem()->getProductOptions();
            if ($options) {
                if (isset($options['shipment_type']) && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY) {
                    return true;
                }
            }
            return false;
        }

        return $process($item);
    }

    /**
     * @param Renderer $object
     * @param Closure $process
     * @param $item
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundIsChildCalculated(Renderer $object, Closure $process, $item = null)
    {
        $order = $this->getOrder();
        if ($this->helperData->isEnabled() && (int)$order->getMpIsEditOrder() === 1) {
            if ($item) {
                $itemData = null;
                if ($item->getOrderItem()) {
                    $itemData = $item->getOrderItemData();
                    $item     = $item->getOrderItem();
                }
                $parentItem = $item->getParentItem();
                if ($parentItem) {
                    if (empty(get_class_methods($parentItem)) && $itemData != null) {
                        $orderItem = (array) json_decode($itemData);
                        $options   = (array) $orderItem['product_options'];
                    } else {
                        $options   = $parentItem->getProductOptions();
                    }
                    if ($options) {
                        return (isset($options['product_calculations'])
                            && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                    }
                } else {
                    $options = $item->getProductOptions();
                    if ($options) {
                        return !(isset($options['product_calculations'])
                            && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                    }
                }
            }

            $options = $object->getOrderItem()->getProductOptions();
            if ($options) {
                if (isset($options['product_calculations'])
                    && $options['product_calculations'] == AbstractType::CALCULATE_CHILD
                ) {
                    return true;
                }
            }
            return false;
        }

        return $process($item);
    }

    /**
     * @param Renderer $object
     * @param Closure $process
     * @param $item
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetSelectionAttributes(Renderer $object, Closure $process, $item)
    {
        $order = $this->getOrder();
        if ($this->helperData->isEnabled() && (int)$order->getMpIsEditOrder() === 1) {
            $itemData = null;
            if ($item instanceof \Magento\Sales\Model\Order\Item) {
                $options  = $item->getProductOptions();
            } else {
                $itemData = $item->getOrderItemData();
                $options  = $item->getOrderItem()->getProductOptions();
            }

            if (empty(get_class_methods($options)) && $itemData != null) {
                $orderItem = (array) json_decode($itemData);
                $options   = (array) $orderItem['product_options'];
            }

            if (isset($options['bundle_selection_attributes'])) {
                return $this->serializer->unserialize($options['bundle_selection_attributes']);
            }
            return null;
        }

        return $process($item);
    }

    /**
     * @param Renderer $object
     * @param Closure $process
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetOrderOptions(Renderer $object, Closure $process)
    {
        $order = $this->getOrder();
        if ($this->helperData->isEnabled() && (int)$order->getMpIsEditOrder() === 1) {
            $result = [];
            $itemData = $object->getOrderItemData();
            if ($itemData != null) {
                $orderItem = (array) json_decode($itemData);
                $options   = (array) $orderItem['product_options'];
            } else {
                $options   = $object->getOrderItem()->getProductOptions();
            }

            if ($options) {
                if (isset($options['options'])) {
                    $result = array_merge($result, $options['options']);
                }
                if (isset($options['additional_options'])) {
                    $result = array_merge($result, $options['additional_options']);
                }
                if (!empty($options['attributes_info'])) {
                    $result = array_merge($options['attributes_info'], $result);
                }
            }
            return $result;
        }

        return $process();
    }

    /**
     * @param null $shipmentId
     * @return Shipment|string
     */
    public function getShipmentById($shipmentId = null)
    {
        if ($shipmentId) {
            return $this->_shipment->load($shipmentId);
        }

        return '';
    }
}
