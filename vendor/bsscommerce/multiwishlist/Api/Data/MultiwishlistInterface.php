<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Api\Data;

/**
 * Interface MultiwishlistInterface
 *
 * @package Bss\MultiWishlist\Api\Data
 */
interface MultiwishlistInterface
{
    /**
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const MULTI_WISHLIST_ID   = 'multi_wishlist_id';
    const CUSTOMER_ID             = 'customer_id';
    const WISHLIST_NAME       = 'wishlist_name';

    /**
     * Get Multi wishlist Id
     *
     * @return int
     */
    public function getMultiWishlistId();

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Get wishlist name
     *
     * @return string
     */
    public function getWishlistName();

    /**
     * Set multi wish list id
     *
     * @param int $multiWishlistId
     * @return $this
     */
    public function setMultiWishlistId($multiWishlistId);

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Set wishlist name
     *
     * @param string $name
     * @return $this
     */
    public function setWishlistName($name);
}
