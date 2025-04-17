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

namespace Mageplaza\EditOrder\Plugin\Model\Quote;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote as ModelQuote;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class Quote
 * @package Mageplaza\EditOrder\Plugin\Model\Quote
 */
class Quote
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ToOrderItem constructor.
     *
     * @param HelperData $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        RequestInterface $request
    ) {
        $this->_helperData = $helperData;
        $this->request     = $request;
    }

    public function beforeAddProduct(ModelQuote $subject, Product $product)
    {
        $actionNames = ['mpeditorder_items_form', 'mpeditorder_order_quick',
            'mpeditorder_payment_method_listMethod', 'mpeditorder_shipping_method_listMethod'];
        if (in_array($this->request->getFullActionName(), $actionNames) && !$product->isSalable()) {
            $product->setSalable(true);
        }
    }
}
