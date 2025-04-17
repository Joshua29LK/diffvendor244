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
 * @package    Bss_CategoriesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CategoriesImportExport\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Export
 *
 * @package Bss\CategoriesImportExport\Model\ResourceModel
 */
class Export
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Export constructor.
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\ProductMetadataInterface $metadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->metadata = $metadata;
        $this->storeManager = $storeManager;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
    }

    /**
     * Get Categories
     *
     * @param array $requestData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCategories($requestData)
    {
        $categoryCollection = $this->collectionFactory->create();
        $categoryIds = $categoryCollection->getAllIds();

        $categoryData = [];
        $stores = $this->getAllStores();
        if ($requestData['export-by'] == 'all') {
            if ($requestData["store-id"] != 'all') {
                $rootId = $this->storeManager->getStore($requestData['store-id'])->getRootCategoryId();
                $currentCategory = $categoryCollection->addAttributeToSelect('*')
                    ->addAttributeToFilter('path', ['like' => "1/{$rootId}%"]);
                foreach ($currentCategory as $category) {
                    $addData = $category->getData();
                    $addData['store_id'] = $requestData['store-id'];
                    $categoryData[$category->getId()] = $addData;
                }
            } else {
                foreach ($categoryIds as $categoryId) {
                    foreach ($stores as $storeId) {
                        $currentCategory = $this->collectionFactory->create()
                            ->setStoreId($storeId)->addIdFilter([$categoryId])
                            ->addAttributeToSelect('*')->getLastItem();
                        $addData = $currentCategory->getData();
                        $addData['store_id'] = $storeId;
                        $categoryData[] = $addData;
                    }
                }
            }
        } else {
            if ($requestData['export-by'] == 'store-id') {
                if ($requestData["store-id"] != 'all') {
                    $rootId = $this->storeManager->getStore($requestData['store-id'])->getRootCategoryId();
                    $currentCategory = $categoryCollection->setStoreId($requestData['store-id'])
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('path', ['like' => "1/{$rootId}%"]);
                    foreach ($currentCategory as $category) {
                        $addData = $category->getData();
                        $addData['store_id'] = $requestData['store-id'];
                        $categoryData[$category->getId()] = $addData;
                    }
                } else {
                    foreach ($categoryIds as $categoryId) {
                        $currentCategory = $this->collectionFactory->create()->setStoreId($requestData['store-id'])
                            ->addIdFilter([$categoryId])->addAttributeToSelect('*')->getLastItem();
                        $addData = $currentCategory->getData();
                        $addData['store_id'] = $requestData['store-id'];
                        $categoryData[$categoryId] = $addData;
                    }
                }
            }
            if (($requestData['export-by'] == 'category-id')) {
                $categoryIds = explode(',', $requestData['category-id']);
                foreach ($categoryIds as $categoryId) {
                    foreach ($stores as $storeId) {
                        $currentCategory = $this->collectionFactory->create()->setStoreId($storeId)
                            ->addIdFilter([$categoryId])->addAttributeToSelect('*')->getLastItem();
                        $addData = $currentCategory->getData();
                        $categoryData[] = $this->addDataStoreId($addData, $storeId);
                    }
                }
            }
        }
        ksort($categoryData);

        return $categoryData;
    }

    /**
     * @param array $addData
     * @param int $storeId
     * @return mixed
     */
    protected function addDataStoreId($addData, $storeId)
    {
        if (!empty($addData)) {
            $addData['store_id'] = $storeId;
        }
        return $addData;
    }

    /**
     * Get all stores
     *
     * @return array
     */
    protected function getAllStores()
    {
        $select = $this->readAdapter->select()->from(
            ['store' => $this->getTableName('store')],
            ['store_id', 'code']
        );
        $stores =  $this->readAdapter->query($select);
        $storeIds = [];
        foreach ($stores as $store) {
            $storeIds[] = $store['store_id'];
        }
        return $storeIds;
    }

    /**
     * Get table
     *
     * @param string $entity
     * @return bool|mixed
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Format date
     *
     * @param string $dateTime
     * @return string
     */
    public function formatDate($dateTime)
    {
        $dateTimeAsTimeZone = $this->timezone
            ->date($dateTime)
            ->format('YmdHis');
        return $dateTimeAsTimeZone;
    }

    /**
     * Get export data
     *
     * @param array $categories
     * @param array $requestData
     * @return array
     */
    public function getExportData($categories, $requestData)
    {
        $data[0] = [
            'category_id',
            'store_id',
            'parent_id',
            'path',
            'name',
            'description',
            'custom_design',
            'category_products',
            'attribute_set_id',
            'position',
            'url_key',
            'url_path',
            'image',
            'is_active',
            'include_in_menu',
            'landing_page',
            'display_mode',
            'page_layout',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'custom_design_from',
            'custom_design_to',
            'default_sort_by',
            'available_sort_by',
            'is_anchor',
            'custom_use_parent_settings',
            'layered_navigation_price_step',
            'custom_apply_to_products',
            'custom_layout_update'
        ];

        foreach ($categories as $category) {
            if (empty($category)) {
                continue;
            }
            $row = null;

            foreach ($data[0] as $key => $attributeCode) {
                if (isset($category[$attributeCode])) {
                    $row[$key] = $category[$attributeCode];
                } else {
                    $row[$key] = "";
                }
            }

            $row[0] = $category['entity_id'];

            if (isset($row[3])) {
                $row[3] = rtrim($row[3], $row[0]);
                $row[3] = rtrim($row[3], "/");
            }

            if (isset($category['entity_id'])) {
                $row[7] = $requestData['export-related-skus'] == 0 ? "" : $this
                    ->getCategoryProducts($category['entity_id']);
            }

            if (isset($row[24])) {
                $row[24] = str_replace(',', '|', $row[24]);
            }

            $data[] = $row;
        }
        return $data;
    }

    /**
     * Get category products
     *
     * @param int $categoryId
     * @return string
     */
    protected function getCategoryProducts($categoryId)
    {
        $select = $this->readAdapter->select()->from(
            ['category_product' => $this->getTableName('catalog_category_product')],
            ['entity_id']
        )->join(
            ['product' => $this->getTableName('catalog_product_entity')],
            'category_product.product_id = product.entity_id',
            ['sku']
        )->where('category_product.category_id = :category_id');

        $bind = [
            ':category_id' => $categoryId
        ];

        $categoryProducts = $this->readAdapter->query($select, $bind);

        $productSkus = "";

        foreach ($categoryProducts as $categoryProduct) {
            $productSkus .= $categoryProduct['sku'] . "|";
        }

        return rtrim($productSkus, "|");
    }

    /**
     * Is enter prise
     *
     * @return bool
     */
    protected function isEnterprise()
    {
        if ($this->metadata->getEdition() == "Enterprise") {
            return true;
        }

        return false;
    }
}
