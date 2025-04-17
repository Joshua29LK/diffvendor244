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
namespace Bss\MultiWishlist\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class WishlistLabel
 *
 * @package Bss\MultiWishlist\Model
 */
class WishlistLabel extends AbstractModel implements \Bss\MultiWishlist\Api\Data\MultiwishlistInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Bss\MultiWishlist\Model\ResourceModel\WishlistLabel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiWishlistId()
    {
        return $this->getData(self::MULTI_WISHLIST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getWishlistName()
    {
        return $this->getData(self::WISHLIST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiWishlistId($multiWishlistId)
    {
        return $this->setData(self::MULTI_WISHLIST_ID, $multiWishlistId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setWishlistName($name)
    {
        return $this->setData(self::WISHLIST_NAME, $name);
    }
}
