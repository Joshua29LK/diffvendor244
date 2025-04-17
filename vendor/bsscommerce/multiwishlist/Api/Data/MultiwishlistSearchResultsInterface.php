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
namespace Bss\MultiWishlist\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface MultiwishlistSearchResultsInterface
 *
 * @package Bss\MultiWishlist\Api\Data
 */
interface MultiwishlistSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\MultiWishlist\Api\Data\MultiwishlistInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
