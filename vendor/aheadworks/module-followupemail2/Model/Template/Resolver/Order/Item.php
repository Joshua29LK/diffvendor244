<?php
namespace Aheadworks\Followupemail2\Model\Template\Resolver\Order;

use Aheadworks\Followupemail2\Model\Template\Resolver\ProductResolverInterface;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class OrderItem
 *
 * @package Aheadworks\Followupemail2\Model\Template\Resolver\Order
 */
class Item implements ProductResolverInterface
{
    /**
     * @inheritDoc
     */
    public function getProductId($orderItem)
    {
        $productId = $orderItem->getProductId();

        $superProductConfig = $orderItem->getProductOptionByCode('super_product_config');
        if (is_array($superProductConfig) && isset($superProductConfig['product_id'])) {
            $productId = $superProductConfig['product_id'];
        }

        if ($orderItem->getParentItem()) {
            $productId = $orderItem->getParentItem()->getProductId();
        }

        return $productId;
    }
}
