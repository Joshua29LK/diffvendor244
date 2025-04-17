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

namespace Mageplaza\EditOrder\Plugin\Block\Adminhtml\Order\Create\Item;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CustomTax
 * @package Mageplaza\EditOrder\Plugin\Block\Adminhtml\Order\Create\Item
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
     * CustomTax constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData
    ) {
        $this->_request    = $request;
        $this->_helperData = $helperData;
    }

    /**
     * @param Grid $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSubtotal(Grid $subject, $result)
    {
        $quote                      = $subject->getQuote();
        $newSubtotalInclTax         = 0;
        $newSubtotal                = 0;
        $newTaxAmount               = 0;
        $newDiscount                = 0;
        $newDiscountTaxCompensation = 0;

        $order = ($this->_request->getParam('order_id')) ? $this->_helperData->getOrderById($this->_request->getParam('order_id')) : null;
        $orderStatus = $order ? $order->getStatus() : 'mp_disable';

        if (!$this->_helperData->isEnabled($quote->getStoreId()) ||
            !$subject->displayTotalsIncludeTax() ||
            !in_array($orderStatus, $this->_helperData->getEditableOrderStatus())) {
            return $result;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            $newSubtotalInclTax         += $item->getRowTotalInclTax();
            $newSubtotal                += $item->getRowTotal();
            $newTaxAmount               += $item->getTaxAmount();
            $newDiscount                += $item->getDiscountAmount();
            $newDiscountTaxCompensation += $item->getDiscountTaxCompensationAmount();
        }

        $address = $subject->getQuoteAddress();

        if ($address->getSubtotalInclTax()) {
            $address->setSubtotal($newSubtotal);
            $address->setTaxAmount($newTaxAmount);
            $address->setDiscountAmount($newDiscount);
            $address->setDiscountTaxCompensationAmount($newDiscountTaxCompensation);

            return $newSubtotalInclTax;
        }

        return $result;
    }
}
