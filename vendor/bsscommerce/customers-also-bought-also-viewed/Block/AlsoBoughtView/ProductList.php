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
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Block\AlsoBoughtView;

use Magento\Checkout\Model\Session;

/**
 * Class ProductList
 * @package Bss\CABAV\Block\AlsoBoughtView
 */
class ProductList extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Bss\CABAV\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    public $postHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    public $compareHelper;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * ProductList constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postHelper
     * @param \Bss\CABAV\Helper\Data $helper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \Bss\CABAV\Helper\Data $helper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        Session $session,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->postHelper = $postHelper;
        $this->formKey = $formKey;
        $this->session = $session;
        $this->compareHelper = $context->getCompareProduct();
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->widgetConfig('title');
    }

    /**
     * @return bool
     */
    public function isMode()
    {
        return $this->widgetConfig('display');
    }

    /**
     * @return mixed
     */
    public function isMaxRow()
    {
        return $this->widgetConfig('number_product');
    }

    /**
     * @return bool
     */
    public function isShowInStock()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isShowAddToCart()
    {
        return $this->widgetConfig('add_to_cart');
    }

    /**
     * @return bool
     */
    public function isShowAddToWishList()
    {
        return $this->widgetConfig('add_to_wishlist');
    }

    /**
     * @return bool
     */
    public function isShowAddToCompare()
    {
        return $this->widgetConfig('add_to_compare');
    }

    /**
     * @return bool
     */
    public function isShowReviews()
    {
        return $this->widgetConfig('review_link');
    }

    /**
     * @param $key
     * @return mixed
     */
    public function widgetConfig($key)
    {
        $widgetConfig = $this->getWidgetConfig();
        return $widgetConfig[$key];
    }

    /**
     * Get Product Price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataPost()
    {
        $data['action'] = $this->getUrl('viewed/cart/add');
        $data['form_key'] = $this->formKey->getFormKey();
        $data['current_url'] = $this->getCurrentUrl();
        return $data;
    }
}
