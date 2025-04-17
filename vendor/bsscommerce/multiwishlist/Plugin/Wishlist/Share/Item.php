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
namespace Bss\MultiWishlist\Plugin\Wishlist\Share;

/**
 * Class Item
 *
 * @package Bss\MultiWishlist\Plugin\Wishlist\Share
 */
class Item
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Wishlist\Controller\Shared\WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Item constructor.
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Wishlist\Controller\Shared\WishlistProvider $wishlistProvider
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Wishlist\Controller\Shared\WishlistProvider $wishlistProvider,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->wishlistProvider = $wishlistProvider;
        $this->request = $request;
    }

    /**
     * Get wishlist Item collection Shared
     *
     * @return int
     */
    protected function getItemCollection()
    {
        $customerId = $this->wishlistProvider->getWishlist()->getCustomerId();
        $multiWishlistId = $this->request->getParam('mwishlist_id');
        $itemCollection = $this->helper->getWishlistItemCollectionShared($multiWishlistId, $customerId);
        return $itemCollection;
    }

    /**
     * Count wishlist item
     *
     * @param \Magento\Wishlist\Block\Share\Wishlist $subject
     * @param mixed $result
     * @return int|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterHasWishlistItems(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        return count($this->getItemCollection());
    }

    /**
     * Get wishlist items
     *
     * @param \Magento\Wishlist\Block\Share\Wishlist $subject
     * @param mixed $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetWishlistItems(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        return $this->getItemCollection();
    }

    /**
     * Set header to wihslist page
     *
     * @param \Magento\Wishlist\Block\Share\Wishlist $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetHeader(\Magento\Wishlist\Block\Share\Wishlist $subject, $result)
    {
        $multiWishlistId = $this->request->getParam('mwishlist_id');
        $wishlistName = $this->helper->getWishlistName($multiWishlistId);
        $result = $result . " (" . __($wishlistName) . ")";
        return $result;
    }
}
