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
use Bss\CheckoutSuccessPage\Helper\Order;

class Suggestion extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/suggestion.phtml";

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var Order
     */
    protected $helperOrder;

    /**
     * Suggestion constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param Data $helper
     * @param Order $helperOrder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        Data $helper,
        Order $helperOrder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->helperOrder = $helperOrder;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return mixed
     */
    public function getSuggestType()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/suggestion/type');
    }

    /**
     * @return mixed || bool
     */
    public function getOrder()
    {
        return $this->helperOrder->getOrder();
    }

    /**
     * @return mixed
     */
    public function getMediaBaseUrl()
    {
        return $this->helper->getMediaBaseUrl();
    }

    /**
     * @param $id
     * @return object
     */
    public function getItemById($id)
    {
        return $this->helperOrder->getItemById($id);
    }

    /**
     * @param $product
     * @return string
     */
    public function getImageThumb($product)
    {
        return $this->imageHelper->init($product, 'product_page_image_medium_no_frame')
        ->setImageFile($product->getImage())
        ->getUrl();
    }
}
