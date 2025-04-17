<?php
namespace Aheadworks\Followupemail2\Model\Template\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class Composite
 */
class Product implements ProductResolverInterface
{
    /**
     * @var ProductResolverInterface[]
     */
    private $resolvers = [];

    /**
     * Composite constructor.
     * @param array $resolvers
     */
    public function __construct(
        $resolvers = []
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     */
    public function getProductId($source)
    {
        $type = $source->getEventPrefix();
        $resolver = $this->resolvers[$type] ?? null;

        if ($resolver instanceof ProductResolverInterface) {
            $productId = $resolver->getProductId($source);
        } else {
            throw new \Exception(__('Wrong object type to take a product URL from'));
        }

        return $productId;
    }
}