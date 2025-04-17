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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteItemToOrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class ToOrderItem
 * @package Mageplaza\EditOrder\Plugin\Model\Quote\Item
 */
class ToOrderItem
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
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * ToOrderItem constructor.
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_request      = $request;
        $this->_helperData   = $helperData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param QuoteItemToOrderItem $subject
     * @param $result
     * @param Item|AddressItem $item
     * @param array $data
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function afterConvert(QuoteItemToOrderItem $subject, $result, $item, $data = [])
    {
        $order = ($this->_request->getParam('order_id')) ? $this->_helperData->getOrderById($this->_request->getParam('order_id')) : null;
        $orderStatus = $order ? $order->getStatus() : 'mp_disable';

        if (!$this->_helperData->isEnabled($item->getStoreId()) ||
            !in_array($orderStatus, $this->_helperData->getEditableOrderStatus())) {

            return $result;
        }

        $result->setOriginalPrice($this->priceCurrency->convert($item->getProduct()->getPrice(), $item->getStore()));
        $result->setBaseOriginalPrice($item->getProduct()->getPrice());
        $result->setBaseTaxAmount($item->getTaxAmount() / $this->getRate($item));
        $result->setTaxAmount($item->getTaxAmount());
        $result->setDiscountAmount($item->getDiscountAmount());
        $result->setBaseDiscountAmount($item->getDiscountAmount() / $this->getRate($item));
        $result->setMpCustomTaxPercent($item->getMpCustomTaxPercent());
        $result->setMpCustomDiscountType($item->getMpCustomDiscountType());
        $result->setMpCustomDiscountValue($item->getMpCustomDiscountValue());
        return $result;
    }

    /**
     * @param $item
     * @return float
     */
    private function getRate($item)
    {
        return $item->getStore()->getCurrentCurrencyRate();
    }
}
