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

namespace Mageplaza\EditOrder\Plugin\Shipment;

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ItemFactory;

/**
 * Class Item
 * @package Mageplaza\EditOrder\Plugin\Shipment
 */
class Item
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_orderItem = null;

    /**
     * Item constructor.
     * @param RequestInterface $request
     * @param OrderFactory $orderFactory
     * @param ItemFactory $orderItemFactory
     */
    public function __construct(
        RequestInterface $request,
        OrderFactory $orderFactory,
        ItemFactory $orderItemFactory
    ) {
        $this->_request     = $request;
        $this->orderFactory = $orderFactory;
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * @param ShipmentItem $object
     * @param Closure $process
     * @return \Magento\Sales\Model\Order\Item|mixed|null
     */
    public function aroundGetOrderItem(ShipmentItem $object, Closure $process)
    {
        if (null === $this->_orderItem) {
            if ($object->getShipment()) {
                $this->_orderItem = $object->getShipment()->getOrder()->getItemById($object->getOrderItemId());
            } else {
                $this->_orderItem = $this->_orderItemFactory->create()->load($object->getOrderItemId());
            }
        }

        if ($this->_request->getParam('order_id')) {
            $orderId = $this->_request->getParam('order_id');
        } elseif ($object->getShipment()) {
            $orderId = $object->getShipment()->getOrderId();
        }

        if (isset($orderId)) {
            $order   = $this->orderFactory->create()->load($orderId);
            if ((int) $order->getMpIsEditOrder() === 1) {
                if ($object->getOrderItemData()) {
                    $orderItem = $this->_orderItemFactory->create();
                    $itemData = json_decode($object->getOrderItemData());
                    foreach ($itemData as $key => $item) {
                        if ($key === 'product_options') {
                            if (getType($item) === 'object') {
                                $convertValue = [];
                                $convert = (array) $item;
                                foreach ($convert as $k => $value) {
                                    if (getType($value) === 'object') {
                                        $convertValue[$k] = (array) $value;
                                    } else {
                                        $convertValue[$k] = $value;
                                    }
                                }
                                $orderItem->setData($key, $convertValue);
                            }
                        } else {
                            $orderItem->setData($key, $item);
                        }
                    }
                    $this->_orderItem = $orderItem;
                    return $this->_orderItem;
                }
            }
        }


        return $process();
    }
}
