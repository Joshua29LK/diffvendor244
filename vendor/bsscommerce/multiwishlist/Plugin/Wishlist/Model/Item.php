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
namespace Bss\MultiWishlist\Plugin\Wishlist\Model;

/**
 * Class Item
 *
 * @package Bss\MultiWishlist\Plugin\Wishlist\Model
 */
class Item
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Item constructor.
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Remove item after add to cart if config module enable
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @param mixed $cart
     * @param bool $delete
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddToCart(\Magento\Wishlist\Model\Item $item, $cart, $delete = false)
    {
        if ($this->helper->isEnable()) {
            $delete = $this->helper->removeItemAfterAddCart();
        }
        return [$cart, $delete];
    }

    /**
     * Around represent product
     *
     * @param \Magento\Wishlist\Model\Item $subject
     * @param bool $result
     * @return bool
     */
    public function afterRepresentProduct(\Magento\Wishlist\Model\Item $subject, $result)
    {
        if ($this->helper->isEnable()) {
            $params = $this->request->getParams();

            $wishlist_id = isset($params['wishlist_id']) ? $params['wishlist_id'] : 0;
            if (is_array($wishlist_id)) {
                $wishlistId = isset($wishlist_id[0]) ? $wishlist_id[0] : 0;
            } else {
                $wishlistId = $wishlist_id;
            }

            if ($result && $subject->getMultiWishlistId() != $wishlistId) {
                $result = false;
            }
        }
        return $result;
    }
}
