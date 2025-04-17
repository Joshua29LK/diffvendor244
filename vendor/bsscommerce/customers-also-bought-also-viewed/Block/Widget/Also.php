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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Also
 *
 * @package Bss\CABAV\Block\Widget
 */
class Also extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var mixed
     */
    protected $json;

    /**
     * @var \Bss\CABAV\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Also constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Bss\CABAV\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Bss\CABAV\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->json = $json;
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return array|bool
     */
    public function getCurrentProduct()
    {
        $currentProduct = $this->registry->registry('product');
        if ($currentProduct && $currentProduct->getId()) {
            $productId = $currentProduct->getId();
            $categoryIds = $currentProduct->getCategoryIds();
            return ['product_id' => $productId, 'categories' => $categoryIds];
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function getWidgetData()
    {
        $data = $this->getData();
        unset($data['module_name']);
        unset($data['type']);
        if (!isset($data['title'])) {
            $data['title'] = __($this->title);
        }
        if (!isset($data['limit_product'])) {
            $data['limit_product'] = 10;
        }
        if (!isset($data['number_product'])) {
            $data['number_product'] = 5;
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getDataJson()
    {
        $data['widget_config'] = $this->getWidgetData();
        if ($this->getCurrentProduct()) {
            $data['current_product'] = $this->getCurrentProduct();
        }
        $data['current_url'] = $this->urlInterface->getCurrentUrl();
        return $this->json->serialize($data);
    }
}
