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

namespace Bss\CABAV\Controller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends Action
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var ResultFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Bss\CABAV\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockFilter;

    /**
     * Product constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param ResultFactory $resultJsonFactory
     * @param \Bss\CABAV\Model\ResourceModel\ConnectionDB $resourceConnection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Bss\CABAV\Helper\Data $helper
     * @param \Magento\CatalogInventory\Helper\Stock $stockFilter
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultFactory $resultJsonFactory,
        \Bss\CABAV\Model\ResourceModel\ConnectionDB $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Bss\CABAV\Helper\Data $helper,
        \Magento\CatalogInventory\Helper\Stock $stockFilter
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productFactory = $productFactory;
        $this->layout = $layout;
        $this->helper = $helper;
        $this->stockFilter = $stockFilter;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create(ResultFactory::TYPE_JSON);
        $data = $this->getRequest()->getPost();
        $widgetConfig = $data['widget_config'];
        $categories = [];
        if (isset($data['current_product'])) {
            $productId = $data['current_product']['product_id'];
            if (isset($data['current_product']['categories'])) {
                $categories = $data['current_product']['categories'];
            } else {
                $widgetConfig['show_category'] = false;
            }
        } else {
            if (!isset($widgetConfig['product_id'])) {
                return $resultJson->setData(['html' => '']);
            }
            $productId = $widgetConfig['product_id'];
            if ($widgetConfig['show_category'] == 1) {
                $product = $this->productFactory->create()->load($productId);
                $categories = $product->getCategoryIds();
            }
        }

        if ($productId == '') {
            return $resultJson->setData(['html' => '']);
        }

        $productList = $this->getProductList($productId);

        if (!$productList || $productList == '') {
            return $resultJson->setData(['html' => '']);
        }

        $collection = $this->getCollection($productList, $widgetConfig, $categories);
        if ($collection->getSize() == 0) {
            return $resultJson->setData(['html' => '']);
        }
        $block = $this->layout->createBlock(\Bss\CABAV\Block\AlsoBoughtView\ProductList::class);
        $block->setTemplate('Bss_CABAV::product/list/items.phtml');
        $block->setItems($collection);
        $block->setWidgetConfig($widgetConfig);
        $block->setTypeBlock($this->type);
        if (isset($data['current_url'])) {
            $block->setCurrentUrl($data['current_url']);
        }
        return $resultJson->setData(['html' => $block->toHtml()]);
    }

    /**
     * @param $productList
     * @param array $widgetConfig
     * @param array $categories
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getCollection($productList, $widgetConfig, $categories)
    {
        $collection = $this->productCollectionFactory->create();

        // filter instock
        if ($widgetConfig['show_instock'] == 1) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }

        $collection->addAttributeToSelect('*');

        // filter product ids
        $collection->addFieldToFilter('entity_id', ['in' => $productList]);

        // filter enable products
        $collection->addAttributeToFilter(
            'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );

        // filter current website products
        $collection->addWebsiteFilter();

        // filter current store products
        $collection->addStoreFilter();

        // set visibility filter
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        // filter categories
        if ($widgetConfig['show_category'] == 1) {
            $collection->addCategoriesFilter(['in' => $categories]);
        }

        // sort collection
        $collection = $this->sortBy($collection, $widgetConfig['sort_by'], $productList);

        // set limit products
        if ($widgetConfig['limit_product'] != '') {
            $collection = $this->resourceConnection->limitCollection($collection, (int)$widgetConfig['limit_product']);
        }
        return $collection;
    }
}
