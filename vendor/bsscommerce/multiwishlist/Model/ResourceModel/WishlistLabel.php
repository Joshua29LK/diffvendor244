<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class WishlistLabel
 *
 * @package Bss\MultiWishlist\Model\ResourceModel
 */
class WishlistLabel extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('bss_multiwishlist', 'multi_wishlist_id');
    }

    /**
     * Delete all items by multiple wishlist id
     *
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection $items
     * @param int $mWishlistId
     * @return $this
     */
    public function deleteItems($items, $mWishlistId)
    {
        if ($items->getSize()) {
            foreach ($items as $item) {
                $table = $item->getResource()->getMainTable();
            }
            $where = ['multi_wishlist_id = ?' => $mWishlistId];
            $this->getConnection()->delete($table, $where);
        }
        return $this;
    }
}
