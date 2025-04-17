<?php
namespace Aheadworks\Followupemail2\Model\Template\Resolver\Product;

use Aheadworks\Followupemail2\Model\Template\Resolver\ProductResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class Item
 */
class Product implements ProductResolverInterface
{
    /**
     * @inheritDoc
     */
    public function getProductId($product)
    {
        return $product->getId();
    }
}