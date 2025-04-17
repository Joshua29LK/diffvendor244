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

namespace Mageplaza\EditOrder\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\EditOrder\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpeditorder';

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var OrderResourceModel
     */
    protected $orderResourceModel;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;


    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestInterface $request
     * @param OrderResourceModel $orderResourceModel
     * @param Order $order
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        RequestInterface $request,
        OrderResourceModel $orderResourceModel,
        Order $order
    ) {
        $this->_order              = $order;
        $this->_request            = $request;
        $this->orderResourceModel  = $orderResourceModel;
        $this->orderRepository     = $orderRepository;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return array|bool
     */
    public function getEditableOrderStatus()
    {
        if ($this->getConfigGeneral('editable_order_status') && $this->isEnabled()) {
            return explode(',', $this->getConfigGeneral('editable_order_status'));
        }

        return false;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabledQuickEdit($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('enabled_quick_edit', $storeId);
    }

    /**
     * @return array|bool
     */
    public function getUpdateAfterEditing()
    {
        if ($this->getConfigGeneral('update_after_editing') && $this->isEnabled()) {
            return explode(',', $this->getConfigGeneral('update_after_editing'));
        }

        return [0];
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRecalculateShippingFee($storeId = null)
    {
        return $this->getConfigGeneral('auto_recalculate_shipping_fee', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isReturnItemToStock($storeId = null)
    {
        return $this->getConfigGeneral('return_item_to_stock', $storeId);
    }

    /**
     * get array different between 2 array
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public function arrayDifferent($array1, $array2)
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2)) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $multidimensionalDiff = $this->arrayDifferent($value, $array2[$key]);
                    if (count($multidimensionalDiff) > 0) {
                        $difference[$key] = $multidimensionalDiff;
                    }
                }
            } else {
                /**
                 * Not the same type
                 */
                if (!array_key_exists($key, $array2) || $array2[$key] != $value) {
                    $difference[$key] = $value;
                }
            }
        }

        return $difference;
    }

    /**
     * Get Edited type for manage logs
     *
     * @param array $diffData
     *
     * @return string
     */
    public function getEditedType($diffData)
    {
        if (isset($diffData['order'])) {
            if (isset($diffData['items']) ||
                count($diffData['order']) > 1 ||
                (count($diffData['order']) === 1 && isset($diffData['payment'])) ||
                (!isset($diffData['order']['shipping_method']) &&
                    count($diffData['order']) === 1 && isset($diffData['method_detail']))
            ) {
                return __('Quick Edit (all)');
            }
        }

        if (isset($diffData['method_detail']) && !isset($diffData['order']) && count($diffData) > 1) {
            return __('Quick Edit (all)');
        }

        $keys = $this->arrayKeysMulti($diffData);

        if (in_array('info', $keys, true)) {
            return __('Order Information');
        }

        if (in_array('customer', $keys, true)) {
            return __('Customer Information');
        }

        if (in_array('billing_address', $keys, true)) {
            return __('Billing Address');
        }

        if (in_array('shipping_address', $keys, true)) {
            return __('Shipping Address');
        }

        if (in_array('method_detail', $keys, true) || in_array('shipping_method', $keys, true)) {
            return __('Shipping Method');
        }

        if (in_array('payment', $keys, true)) {
            return __('Payment Method');
        }

        if (in_array('item', $keys, true)) {
            return __('Items');
        }

        return '';
    }

    /**
     * Get all key array
     *
     * @param array $data
     *
     * @return array
     */
    public function arrayKeysMulti($data)
    {
        $keys     = [];
        $childKey = [];

        foreach ($data as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $childKey = $this->arrayKeysMulti($value);
            }
        }

        return array_merge($keys, $childKey);
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public function isEdited($array)
    {
        $isEdited = false;
        foreach ($array as $key => $value) {
            if ($key === 'payment' && isset($array[$key])) {
                foreach ($array[$key] as $p_key => $p_value) {
                    if ($array[$key][$p_key]) {
                        $isEdited = true;
                        break;
                    }
                }
            } elseif ($array[$key]) {
                $isEdited = true;
                break;
            }
        }

        return $isEdited;
    }

    /**
     * @param $orderId
     * @return Order|string
     */
    public function getOrderById($orderId)
    {
        if ($orderId) {
            return $this->_order->load($orderId);
        }

        return '';
    }

    /**
     * @return mixed|string
     */
    public function getOrderId()
    {
        $orderId = $this->_request->getParam('order_id');
        if ($orderId) {
            return $orderId;
        }

        return '';
    }

    /**
     * @param $order
     * @return bool
     */
    public function canCredit($order)
    {
        $isCheck = false;
        if (empty($order->getInvoiceCollection())) {
                return false;
        }

        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyInvoiced() > 0 && ($item->getQtyInvoiced() > $item->getQtyRefunded())) {
                $isCheck = true;
                break;
            }
        }

        return $isCheck;
    }

    /**
     * @return bool
     */
    public function isEnableInventorySales()
    {
        return $this->isModuleOutputEnabled('Magento_InventorySales');
    }
}
