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

namespace Bss\CABAV\Controller\Alsobought;

use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellersCollection;

class Product extends \Bss\CABAV\Controller\Product
{
    /**
     * @var string
     */
    protected $type = 'alsobought';

    /**
     * @var BestsellersCollection
     */
    protected $bestSellersCollectionFactory;

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
     * @param BestsellersCollection $bestSellersCollectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        BestsellersCollection $bestSellersCollectionFactory
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $resourceConnection,
            $productCollectionFactory,
            $productVisibility,
            $productFactory,
            $layout,
            $helper,
            $stockFilter
        );
        $this->bestSellersCollectionFactory = $bestSellersCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create(ResultFactory::TYPE_JSON);
        $data = $this->getRequest()->getPost();
        $widgetConfig = $data['widget_config'];
        if (isset($data['current_product']) || isset($widgetConfig['product_id'])) {
            return parent::execute();
        } else {
            $productList = $this->getProducts($widgetConfig);

            if (empty($productList)) {
                return $resultJson->setData(['html' => '']);
            }

            $collection = $this->getCollection($productList, $widgetConfig, []);
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
    }

    /**
     * @param array $widgetConfig
     * @return array
     */
    private function getProducts($widgetConfig)
    {
        $limit = 5;
        if (isset($widgetConfig['limit_product']) && $widgetConfig['limit_product'] > 0) {
            $limit = (int) $widgetConfig['limit_product'];
        }
        $productIds = [];
        $bestSellers = $this->bestSellersCollectionFactory->create()->setPeriod('year')->setPageSize($limit);
        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }
        return $productIds;
    }

    /**
     * @param string $productId
     * @return string
     */
    protected function getProductList($productId)
    {
        return $this->resourceConnection->getProductAlsoBought($productId);
    }

    /**
     * @param $collection
     * @param string $sortType
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function sortBy($collection, $sortType, $productList)
    {
        switch ($sortType) {
            case 1:
                $collection->addAttributeToSort('name', 'ASC');
                break;
            case 2:
                $collection->addAttributeToSort('name', 'DESC');
                break;
            case 3:
                $collection->addAttributeToSort('price', 'DESC');
                break;
            case 4:
                $collection->addAttributeToSort('price', 'ASC');
                break;
            case 5:
                $collection = $this->resourceConnection->alsoBoughtJoinLeft($collection);
                break;
            default:
                $collection->getSelect()->orderRand();
        }
        return $collection;
    }
}
