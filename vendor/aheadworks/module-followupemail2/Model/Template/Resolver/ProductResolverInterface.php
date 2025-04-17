<?php
namespace Aheadworks\Followupemail2\Model\Template\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Interface ProductResolverInterface
 */
interface ProductResolverInterface
{
    /**
     * Get product id
     *
     * @param ProductInterface|QuoteItem|OrderItem $source
     * @return int|null
     * @throws \Exception
     */
    public function getProductId($source);
}