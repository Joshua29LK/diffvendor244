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
namespace Bss\MultiWishlist\Model\ResourceModel\WishlistLabel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Bss\MultiWishlist\Model\ResourceModel\WishlistLabel
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'multi_wishlist_id';

    /**
     * Define model & resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\MultiWishlist\Model\WishlistLabel::class,
            \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel::class
        );
    }
}
