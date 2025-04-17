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
namespace Bss\MultiWishlist\Block;

use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\WishlistLabel as Model;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Popup
 *
 * @package Bss\MultiWishlist\Block
 */
class Popup extends Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Popup constructor.
     * @param Template\Context $context
     * @param Helper $helper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * Get bss helper data
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get label collection
     *
     * @return \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\Collection
     */
    public function getMyWishlist()
    {
        return $this->helper->getWishlistLabels();
    }

    /**
     * Check customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->helper->isCustomerLoggedIn();
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getActionWl()
    {
        return $this->getData('action');
    }

    /**
     * Get data unwishlist
     *
     * @return mixed
     */
    public function getUnwishlist()
    {
        return $this->getData('unwishlist');
    }

    /**
     * Get url action frontend
     *
     * @return string
     */
    public function getUrlAction()
    {
        if ($this->getActionWl() == 'add') {
            return $this->getUrl("multiwishlist/index/assignWishlist/");
        }
        if ($this->getActionWl() == 'copy') {
            return $this->getUrl("multiwishlist/index/copy");
        }
        if ($this->getActionWl() == 'move') {
            return $this->getUrl("multiwishlist/index/movetowishlist");
        }
        if ($this->getActionWl() == 'movefromcart') {
            return $this->getUrl("multiwishlist/index/assignWishlistFromCart");
        }
        return '';
    }

    /**
     * Return create wishlist url
     *
     * @return string
     */
    public function getUrlCreateWishList()
    {
        return $this->getUrl("multiwishlist/index/create/ajax/1");
    }
}
