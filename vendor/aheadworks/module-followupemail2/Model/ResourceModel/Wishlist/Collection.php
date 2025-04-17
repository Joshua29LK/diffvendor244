<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Wishlist;

use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Wishlist
 */
class Collection extends WishlistCollection
{
    /**
     * @return $this
     */
    public function addNotEmptyFilter()
    {
        $this->getSelect()
            ->joinLeft(
                ['wic' => new \Zend_Db_Expr(
                    '(SELECT wishlist_id, COUNT(*) as item_count
	                FROM ' . $this->getTable('wishlist_item')
                    . ' GROUP BY wishlist_id)'
                )],
                'wic.wishlist_id=main_table.wishlist_id',
                ['wic.item_count']
            )
            ->where('wic.item_count > 0');

        return $this;
    }
}
