<?php
namespace Aheadworks\Followupemail2\Model\Template\Resolver\Quote;

use Aheadworks\Followupemail2\Model\Template\Resolver\ProductResolverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Item
 *
 * @package Aheadworks\Followupemail2\Model\Template\Resolver\Quote
 */
class Item implements ProductResolverInterface
{
    /**
     * @inheritDoc
     */
    public function getProductId($quoteItem)
    {
        $productId = $quoteItem->getData('product_id');

        $productTypeOption = $quoteItem->getOptionByCode('product_type');
        if ($productTypeOption) {
            $productId = $productTypeOption->getProductId();
        }

        if ($quoteItem->getParentItem()) {
            $productId = $quoteItem->getParentItem()->getData('product_id');
        }

        return $productId;
    }
}
