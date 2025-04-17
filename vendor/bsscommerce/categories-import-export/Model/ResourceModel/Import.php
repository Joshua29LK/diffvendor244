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

/**
 * Class Import
 *
 * @package Bss\CategoriesImportExport\Model\ResourceModel
 */
class Import
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->resource = $resource;
        $this->readAdapter = $this->resource->getConnection('core_read');
        $this->writeAdapter = $this->resource->getConnection('core_write');
        $this->request = $request;
        $this->productModel = $productModel;
        $this->scopeConfig = $scopeConfig;
        $this->productMetadata = $productMetadata;
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
                $this->tableNames[$entity] = $this->resource->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Check exist category
     *
     * @param int $categoryId
     * @return array
     */
    public function checkExistCategory($categoryId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('catalog_category_entity'),
                [
                    'entity_id', 'path'
                ]
            )->where('entity_id = :category_id');
        $bind = [
            ':category_id' => $categoryId
        ];
        $category = $this->readAdapter->fetchRow($select, $bind);
        return $category;
    }

    /**
     * @param string $urlKey
     * @param int $storeId
     * @return boolean
     */
    public function isCategoryUrlKeyExistedWithStore($urlKey, $storeId)
    {
        $categoryUrlSuffix = $this->scopeConfig->getValue(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $urlKey .= $categoryUrlSuffix;
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('url_rewrite'),
                [
                    'entity_id'
                ]
            )->where('store_id = :store_id')
            ->where('request_path = :request_path');
        $bind = [
            ':store_id' => $storeId,
            ':request_path' => $urlKey,
        ];
        return $this->readAdapter->fetchOne($select, $bind);
    }

    /**
     * @param string $urlKey
     * @param int $level
     * @param int $parentId
     * @return boolean
     */
    public function isCategoryUrlKeyExistedInSameLevel($urlKey, $level, $parentId)
    {
        $entityId = $this->productMetadata->getEdition() === 'Community' ? 'entity_id' : 'row_id';
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('catalog_category_entity_varchar')],
                [$entityId, 'store_id']
            )->joinLeft(
                ['category_entity' => $this->getTableName('catalog_category_entity')],
                'category_entity.entity_id = main_table.' . $entityId,
                ['level']
            )
            ->joinLeft(
                ['eav_attribute' => $this->getTableName('eav_attribute')],
                'eav_attribute.attribute_id = main_table.attribute_id',
                ['attribute_code']
            )
            ->where('eav_attribute.attribute_code = ?', 'url_key')
            ->where('category_entity.level = ?', $level)
            ->where('category_entity.parent_id = ?', $parentId)
            ->where('main_table.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            ->where('main_table.value = ?', $urlKey);

        return $this->readAdapter->fetchOne($select);
    }

    /**
     * Set category path
     *
     * @param int $id
     * @param string $path
     * @param string $level
     */
    public function setCategoryPath($id, $path, $level)
    {
        $updateData['path'] = $path;
        $updateData['level'] = $level;
        $condition = ["{$this->getTableName('catalog_category_entity')}.entity_id = ?" => $id];
        return $this->writeAdapter->update($this->getTableName('catalog_category_entity'), $updateData, $condition);
    }

    /**
     * Product to category
     *
     * @param array $productSkus
     * @param int $categoryId
     */
    public function assignProductToCategory($productSkus, $categoryId)
    {
        $storeIds = $this->getAllStoreIds();
        foreach ($productSkus as $productSku) {
            $productId = $this->getProductId($productSku);
            if ($this->isProductExistInCategory($productId, $categoryId) === false && $productId > 0) {
                $productCategoryData = [];
                $productCategoryData['category_id'] = $categoryId;
                $productCategoryData['product_id'] = $productId;
                $productCategoryData['position'] = 0;
                $this->writeAdapter->insert($this->getTableName('catalog_category_product'), $productCategoryData);

                $productCategoryDataIndex = [];
                $productCategoryDataIndex['category_id'] = $categoryId;
                $productCategoryDataIndex['product_id'] = $productId;
                $productCategoryDataIndex['position'] = 0;
                $productCategoryDataIndex['is_parent'] = 1;
                $productCategoryDataIndex['visibility'] = 4;
                foreach ($storeIds as $storeId) {
                    $productCategoryDataIndex['store_id'] = $storeId;
                    $this->writeAdapter->insert($this
                        ->getTableName('catalog_category_product_index'), $productCategoryDataIndex);
                }
            }
        }
    }

    /**
     * Product exist in category
     *
     * @param int $productId
     * @param int $categoryId
     * @return string
     */
    protected function isProductExistInCategory($productId, $categoryId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('catalog_category_product'),
                [
                    'entity_id'
                ]
            )->where('category_id = :category_id')->where('product_id = :product_id');
        $bind = [
            ':category_id' => $categoryId,
            ':product_id' => $productId
        ];
        return $this->readAdapter->fetchOne($select, $bind);
    }

    /**
     * Get all store id
     *
     * @return array
     */
    public function getAllStoreIds()
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('store'),
                [
                    'store_id'
                ]
            )->where('store_id > 0');
        $stores = $this->readAdapter->query($select);
        $storeIds = [];
        foreach ($stores as $store) {
            $storeIds[] = $store['store_id'];
        }
        return $storeIds;
    }

    /**
     * Add category url
     *
     * @param int $categoryId
     * @param int $defaultStoreId
     */
    public function addCategoryUrlRewrite($categoryId, $defaultStoreId)
    {
        $entityType = "category";
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('url_rewrite'),
                ['entity_type', 'entity_id', 'request_path',
                    'target_path', 'redirect_type', 'description', 'is_autogenerated', 'metadata']
            )->where('entity_id = :category_id')->where('entity_type = :entity_type');
        $bind = [
            ':category_id' => $categoryId,
            ':entity_type' => $entityType
        ];

        $urlRewrite = $this->readAdapter->fetchRow($select, $bind);

        foreach ($this->getAllStoreIds() as $storeId) {
            if ($storeId > 0 && $storeId != $defaultStoreId) {
                $urlRewrite['store_id'] = $storeId;
                $this->writeAdapter->insert($this->getTableName('url_rewrite'), $urlRewrite);
            }
        }
    }

    /**
     * Check exist category path
     *
     * @param string $path
     * @return string
     */
    public function checkExistCategoryPath($path)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('catalog_category_entity'),
                [
                    'entity_id'
                ]
            )->where('path = :path');
        $bind = [
            ':path' => $path
        ];
        $categoryId = $this->readAdapter->fetchOne($select, $bind);
        return $categoryId;
    }

    /**
     * Get path
     *
     * @param int $parentId
     * @return string
     */
    public function getPath($parentId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('catalog_category_entity'),
                [
                    'path'
                ]
            )->where('entity_id = :parent_id');
        $bind = [
            ':parent_id' => $parentId
        ];
        $path = $this->readAdapter->fetchOne($select, $bind);
        return $path;
    }

    /**
     * Get product id
     *
     * @param array $productSku
     * @return string
     */
    public function getProductId($productSku)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('catalog_product_entity'),
                [
                    'entity_id'
                ]
            )->where('sku = :sku');
        $bind = [
            ':sku' => $productSku
        ];
        $id = $this->readAdapter->fetchOne($select, $bind);
        return $id;
    }

    /**
     * Get new category id
     *
     * @param int $currentId
     * @param int $newId
     * @return mixed
     */
    public function getNewCategoryId($currentId, $newId)
    {
        $updateData['entity_id'] = $newId;
        $condition = ["{$this->getTableName('catalog_category_entity')}.entity_id = ?" => $currentId];
        $this->writeAdapter->update($this->getTableName('catalog_category_entity'), $updateData, $condition);
        return $newId;
    }
}
