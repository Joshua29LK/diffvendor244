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

namespace Mageplaza\EditOrder\Plugin\Model\Quote\Item;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Config as ConfigTax;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CustomTax
 * @package Mageplaza\EditOrder\Plugin\Model\Quote\Item
 */
class CustomTax
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var bool
     */
    protected $_isError = false;

    /**
     * @var ConfigTax
     */
    protected $config;

    /**
     * CustomTax constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param ManagerInterface $messageManager
     * @param ConfigTax $config
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        ManagerInterface $messageManager,
        ConfigTax $config
    ) {
        $this->_request        = $request;
        $this->_helperData     = $helperData;
        $this->_messageManager = $messageManager;
        $this->config          = $config;
    }

    /**
     * @param CommonTaxCollector $subject
     * @param CommonTaxCollector $result
     * @param AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store $store
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function afterUpdateItemTaxInfo(
        CommonTaxCollector $subject,
        $result,
        $quoteItem,
        $itemTaxDetails,
        $baseItemTaxDetails,
        $store
    ) {
        $order = ($this->_request->getParam('order_id')) ? $this->_helperData->getOrderById($this->_request->getParam('order_id')) : null;
        $orderStatus = $order ? $order->getStatus() : 'mp_disable';
        $configTaxAfterDiscount = $this->config->applyTaxAfterDiscount($store);

        if (!$this->_helperData->isEnabled($store->getStoreId()) ||
            !in_array($orderStatus, $this->_helperData->getEditableOrderStatus())) {
            return $result;
        }

        $request = $this->_request->getParams();
        if (isset($request['item'])) {
            try {
                foreach ($request['item'] as $itemId => $data) {
                    if (!empty($data['tax']) && ($itemId === (int) $quoteItem->getId())) {
                        $itemPriceWithoutDiscount = $quoteItem->getRowTotal() - $quoteItem->getTotalDiscountAmount();
                        $itemPrice                = $quoteItem->getRowTotal();
                        $taxPercent               = $data['tax'];
                        $taxAmount                = $itemPrice * $taxPercent / 100;
                        if ($configTaxAfterDiscount) {
                            $taxAmount = $itemPriceWithoutDiscount * $taxPercent / 100;
                        }

                        $price = $data['custom_price'] ?? $data['price'];

                        $quoteItem->setTaxPercent($taxPercent);
                        $quoteItem->setTaxAmount($taxAmount);
                        $quoteItem->setBaseTaxAmount($taxAmount / $quoteItem->getStore()->getCurrentCurrencyRate());
                        $quoteItem->setMpCustomTaxPercent($taxPercent);
                        $quoteItem->setPriceInclTax($price + $taxAmount / $data['qty']);
                        $quoteItem->setBasePriceInclTax($price + $taxAmount / $data['qty']);
                        $quoteItem->setRowTotalInclTax($quoteItem->getRowTotal() + $taxAmount);
                        $quoteItem->setBaseRowTotalInclTax($quoteItem->getBaseRowTotal() + $taxAmount);
                    }
                }
            } catch (Exception $e) {
                if (!$this->_isError) {
                    $this->_messageManager->addErrorMessage(__('Unknown error, unable to update order.'));
                    $this->_isError = true;
                }
            }
        }

        return $result;
    }
}
