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

use Bss\CheckoutSuccessPage\Block\Cms as BssCms;
use Bss\CheckoutSuccessPage\Block\Cms2 as BssCms2;
use Bss\CheckoutSuccessPage\Block\Coupon as BssCoupon;
use Bss\CheckoutSuccessPage\Block\Social as BssSocial;
use Bss\CheckoutSuccessPage\Block\Subscription as BssSubscription;
use Bss\CheckoutSuccessPage\Block\Suggestion as BssSugestion;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Main extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var array
     */
    protected $parentProductUrl = [];

    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Order
     */
    protected $helperOrder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Bss\CheckoutSuccessPage\Helper\ImageHelper
     */
    protected $imageHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * Main constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\CheckoutSuccessPage\Helper\Data $helper
     * @param \Bss\CheckoutSuccessPage\Helper\Order $helperOrder
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Bss\CheckoutSuccessPage\Helper\ImageHelper $imageHelper
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\CheckoutSuccessPage\Helper\Data $helper,
        \Bss\CheckoutSuccessPage\Helper\Order $helperOrder,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Bss\CheckoutSuccessPage\Helper\ImageHelper $imageHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->session = $session;
        $this->helper = $helper;
        $this->helperOrder = $helperOrder;
        $this->storeManager = $context->getStoreManager();
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->imageHelper = $imageHelper;
        $this->taxHelper = $taxHelper;
        $this->postDataHelper = $postDataHelper;
        $this->timezone = $context->getLocaleDate();
    }

    /**
     * @param $product
     * @return string
     */
    public function getImageThumb($product)
    {
        return $this->imageHelper->getImageThumb($product);
    }

    /**
     * @return mixed
     */
    public function getThanksEnable()
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/thanks/enable');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getThanksMess()
    {
        $content = $this->helper->getConfigValue('checkoutsuccesspage/thanks/mess');
        return $this->helper->getEditor($content);
    }

    /**
     * @return bool
     */
    public function isInforEnable()
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/infor/enable');
    }

    /**
     * @return bool
     */
    public function isInforStatus()
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/infor/status');
    }

    /**
     * @return bool
     */
    public function isInforThumbnail()
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/infor/thumbnail');
    }

    /**
     * @return bool
     */
    public function isInforReorder()
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/infor/reorder');
    }

    /**
     * @param $order
     * @return bool
     */
    public function isInforPrint($order)
    {
        return $this->helper->isConfigEnable('checkoutsuccesspage/infor/print');
    }

    /**
     * @return mixed
     */
    public function getStyleButton()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/button');
    }

    /**
     * @return mixed
     */
    public function getStyleBackground()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/background');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function printBackground()
    {
        if ($this->helper->getConfigValue('checkoutsuccesspage/thanks/background')) {
            return "<img class='thanksbackground'
            src='" . $this->imageHelper->resize(
                $this->helper->getConfigValue(
                        'checkoutsuccesspage/thanks/background'
                    ),
                1500
            ) . "'>";
        }
    }

    /**
     * @return bool
     */
    public function hasBackground()
    {
        if ($this->helper->getConfigValue('checkoutsuccesspage/thanks/background')) {
            return true;
        }
        return false;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->helper->formatPrice($price);
    }

    /**
     * @return \Magento\Sales\Model\Order|mixed
     */
    public function getOrder()
    {
        return $this->helperOrder->getOrder();
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        return $this->helper->getCountryName($countryCode);
    }

    /**
     * @param $date
     * @return string
     */
    public function formatOrderDate($date)
    {
        return $this->formatDate($date);
    }

    /**
     * @param $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemById($item)
    {
        return $this->productRepositoryFactory->create()->getById($item->getProductId());
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->helper->getMediaBaseUrl();
    }

    /**
     * @param $nameBlock
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlock($nameBlock)
    {
        $block = $this->getLayout()->createBlock($nameBlock);
        return $block->toHtml();
    }

    /**
     * @param $order
     * @return bool
     */
    public function noneCoupon($order)
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/coupon/enable')) {
            return true;
        }
        $group = explode(",", $this->helper->getConfigValue('checkoutsuccesspage/coupon/customer'));

        if (!($group[0] == 32000 || in_array($this->helper->getGroupId(), $group))) {
            return true;
        }

        if ($this->helper->getConfigValue('checkoutsuccesspage/coupon/code') == '0') {
            $total = $order->getBaseSubTotal();
            $min = $this->helper->getConfigValue('checkoutsuccesspage/coupon/min');
            if ($total < $min) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function noneSocial()
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/social/enable')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function noneSubs()
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/subs/news')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function noneCms1()
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/cms/enable')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function noneCms2()
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/cms/enable2')) {
            return true;
        }
        return false;
    }

    /**
     * @param $order
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function noneSuggest($order)
    {
        if (!$this->helper->isConfigEnable('checkoutsuccesspage/suggestion/enable')) {
            return true;
        }

        $items = $order->getAllVisibleItems();
        $suggestss = [];
        foreach ($items as $item) {
            $product = $this->productRepositoryFactory->create()->getById($item->getProductId());
            if ($this->helper->getConfigValue('checkoutsuccesspage/suggestion/type') == "related") {
                $suggestss[] = $product->getRelatedProducts();
            } elseif ($this->helper->getConfigValue('checkoutsuccesspage/suggestion/type') == "up") {
                $suggestss[] = $product->getUpSellProducts();
            } else {
                $suggestss[] = $product->getCrossSellProducts();
            }
        }
        $i = 0;

        foreach ($suggestss as $suggests) {
            foreach ($suggests as $suggest) {
                if ($suggest) {
                    $i++;
                }
            }
        }
        if ($i == 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $order
     * @return string
     */
    public function getReorder($order)
    {
        return $this->helperOrder->getReorder($order);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrint($order)
    {
        return $this->helperOrder->getPrint($order);
    }

    /**
     * @param $order
     * @param $billingLastName
     * @return string
     */
    public function getPrintAsGuest($order, $billingLastName)
    {
        $params = [
            'oar_order_id' => $order->getIncrementId(),
            'oar_billing_lastname' => $billingLastName,
            'oar_type' => 'email',
            'oar_email' => $order->getCustomerEmail()
        ];
        $params = base64_encode($this->helper->serializer($params));
        $url = $this->getUrl('checkoutsuccess/guest/view', ['_query' => ['id' => json_encode($params)]]);
        return $url;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkBlock()
    {
        $order = $this->getOrder();
        $sortOrder = [
            0 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/coupon/sort'),
                'block' => BssCoupon::class
            ],
            1 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/subs/sort'),
                'block' => BssSubscription::class
            ],
            2 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/cms/sort1'),
                'block' => BssCms::class
            ],
            3 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/social/sort'),
                'block' => BssSocial::class
            ],
            4 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/suggestion/sort'),
                'block' => BssSugestion::class
            ],
            5 => [
                'sort' => $this->helper->getConfigValue('checkoutsuccesspage/cms/sort2'),
                'block' => BssCms2::class
            ]
        ];

        if ($this->noneCoupon($order)) {
            unset($sortOrder[0]);
        }
        if ($this->noneSubs()) {
            unset($sortOrder[1]);
        }
        if ($this->noneCms1()) {
            unset($sortOrder[2]);
        }
        if ($this->noneSocial()) {
            unset($sortOrder[3]);
        }
        if ($this->noneSuggest($order)) {
            unset($sortOrder[4]);
        }
        if ($this->noneCms2()) {
            unset($sortOrder[5]);
        }

        return $sortOrder;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function lineUp()
    {
        $order = $this->checkBlock();
        foreach ($order as $key => $row) {
            $sort[$key] = $row["sort"];
        }

        if ($order) {
            array_multisort($sort, SORT_ASC, $order);
        }

        $sort = '<table class="table-sort">';
        $i = 0;
        foreach ($order as $ord) {
            $i++;
            if ($i % 2 == 0) {
                $sort .= "<div class='half-a-page half-right'>" . $this->getBlock($ord['block']) . "</div></td></tr>";
            } else {
                $sort .= "<tr><td><div class='half-a-page half-left'>" . $this->getBlock($ord['block']) . "</div>";
            }
        }
        $sort .= '</table>';
        return $sort;
    }

    /**
     * @return mixed
     */
    public function getSuccessText()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/success_text');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSuccessIcon()
    {
        if ($this->helper->getConfigValue('checkoutsuccesspage/style/success_icon')) {
            $imageUrl = $this->helper->getConfigValue('checkoutsuccesspage/style/success_icon');
            $resizeUrl = $this->imageHelper->resize($imageUrl, 48, 48);
            return $resizeUrl;
        }
    }

    /**
     * @return mixed
     */
    public function getAddressTextColor()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/address_method_text_color');
    }

    /**
     * @return mixed
     */
    public function getAddressBorderColor()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/address_method_border');
    }

    /**
     * @return mixed
     */
    public function getAddressButtonColor()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/address_method_button_color');
    }

    public function getButtonHoverColor()
    {
        return $this->helper->getConfigValue('checkoutsuccesspage/style/button_hover');
    }

    /**
     * Return whether display setting is to display price including tax
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displayPriceInclTax()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->taxHelper->displaySalesPriceInclTax($storeId);
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displayPriceExclTax()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->taxHelper->displaySalesPriceExclTax($storeId);
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function displayBothPrices()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->taxHelper->displaySalesBothPrices($storeId);
    }

    /**
     * @return $this|\Magento\Sales\Block\Order\Totals
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];
        $this->_totals['subtotal'] = $this->dataObjectFactory->create()->setData(
            ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        );

        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((double)$source->getShippingAmount() || $source->getShippingDescription())) {
            $this->_totals['shipping'] = $this->dataObjectFactory->create()->setData(
                [
                    'code' => 'shipping',
                    'field' => 'shipping_amount',
                    'value' => $this->getSource()->getShippingAmount(),
                    'label' => __('Shipping & Handling'),
                ]
            );
        }
        if ($this->getSource()->getBaseBssStorecreditAmount() > 0) {
            $this->_totals['store_credit'] = $this->dataObjectFactory->create()->setData(
                [
                    'code' => 'store_credit',
                    'field' => 'grand_total',
                    'value' => -$source->getBaseBssStorecreditAmount(),
                    'label' => __('Store Credit'),
                ]
            );
        }
        /**
         * Add discount
         */
        if ((double)$this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $source->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = $this->dataObjectFactory->create()->setData(
                [
                    'code' => 'discount',
                    'field' => 'discount_amount',
                    'value' => $source->getDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        $this->_totals['grand_total'] = $this->dataObjectFactory->create()->setData(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        return $this;
    }

    /**
     * @param $productOption
     * @return bool|mixed
     */
    public function getParentProductUrl($productOption)
    {
        if ($productOption &&
            isset($productOption['super_product_config']) &&
            isset($productOption['super_product_config']['product_type']) &&
            $productOption['super_product_config']['product_type'] == 'grouped' &&
            isset($productOption['super_product_config']['product_id'])
        ) {
            $id = $productOption['super_product_config']['product_id'];
        } else {
            return false;
        }

        if (isset($this->parentProductUrl[$id])) {
            return $this->parentProductUrl[$id];
        }
        try {
            $product = $this->productRepositoryFactory->create()->getById($id);
            $this->parentProductUrl[$id] = $product->getProductUrl();
            return $product->getProductUrl();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Order View Url
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        if ($this->session->isLoggedIn()) {
            return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
        }
        return $this->getUrl('sales/guest/form/');
    }
}
