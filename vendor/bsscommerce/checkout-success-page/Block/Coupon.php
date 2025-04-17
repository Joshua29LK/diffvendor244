<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Block;

use Bss\CheckoutSuccessPage\Helper\Data;
use Bss\CheckoutSuccessPage\Helper\ImageHelper;

/**
 * Class Coupon
 * @package Bss\CheckoutSuccessPage\Block
 */
class Coupon extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/coupon.phtml";

    /**
     * @var Data
     */
    protected $helper;

    protected $imageHelper;

    /**
     * Coupon constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return string
     */
    public function getCouponBg()
    {
        if ($this->helper->getConfigValue('checkoutsuccesspage/coupon/background')) {
            return "<img class='coupon-bg' src='".$this->imageHelper->resize($this->helper->getConfigValue('checkoutsuccesspage/coupon/background'), 700)."'>";
        }
    }

    /**
     * @return string
     */
    public function getCouponDescription()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/coupon/description');
    }

    /**
     * @return integer
     */
    public function getCouponCode()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/coupon/code');
    }

    /**
     * @return mixed
     */
    public function getCouponCustom()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/coupon/customcode');
    }
}
