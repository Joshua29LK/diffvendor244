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
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class MultiWishlist
 *
 * @package Bss\MultiWishlist\Block
 */
class MultiWishlist extends Template
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * MultiWishlist constructor.
     * @param Context $context
     * @param Helper $helper
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->formKey = $formKey;
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
     * Check customer id logged
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->helper->isCustomerLoggedIn();
    }

    /**
     * Is Redirect
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->helper->isRedirect();
    }

    /**
     * Get wishlist url
     *
     * @return string
     */
    public function getUrlWishlist()
    {
        return $this->getUrl("wishlist");
    }

    /**
     * Get popup url
     *
     * @return string
     */
    public function getUrlPopup()
    {
        return $this->getUrl("multiwishlist/index/popup");
    }

    /**
     * Get Form Key
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
