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
namespace Bss\CategoriesImportExport\Model\Import;

use Bss\CategoriesImportExport\Model\Import as BssImport;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class Category
 *
 * @package Bss\CategoriesImportExport\Model\Import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Category extends Import\Entity\AbstractEntity
{
    const COL_USER_EMAIL = 'user_email';

    const COL_PRODUCT_SKU = 'product_sku';

    const VALIDATOR_MAIN = 'validator';

    const DEFAULT_OPTION_VALUE_SEPARATOR = ';';

    protected $newCategoryIds = [];

    const ERROR_INVALID_PATH = 'Invalid Category Path';
    const ERROR_EMPTY_PARENT_ID = 'Empty Parent ID';
    const ERROR_EXIST_URL_KEY = 'URL key for specified store already exists';
    const ERROR_EMPTY_CATEGORY_NAME = 'Empty category name';
    const ERROR_EMPTY_STORE_ID = 'Empty store ID';
    const ERROR_CATEGORY_ID_NOT_EXIST = 'Category ID is not existing';
    const ERROR_PARENT_ID_NOT_EXIST = 'Parent category ID is not existing';
    const ERROR_PATH = 'Invalid Path';
    const ERROR_WRONG_DATE_FORMAT = 'Wrong format in column custom_design_from or custom_design_to';

    protected $rootCateList = [];

    protected $rowSuccess = [];

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $swatchAttributes = [];

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var array
     */
    protected $validColumnNames = [
        'category_id',
        'store_id',
        'parent_id',
        'path',
        'name',
        'description',
        'category_products',
        'attribute_set_id',
        'position',
        'url_key',
        'url_path',
        'image',
        'is_active',
        'custom_design',
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
        'custom_layout_update',
        'ox_nav_type',
        'ox_columns',
        'ox_cat_image',
        'ox_nav_btm'
    ];

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var array
     */
    protected $existUrls = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Bss\CategoriesImportExport\Model\ResourceModel\Import
     */
    protected $import;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $importProductModel;

    /**
     * @var Category\Validator\Category
     */
    protected $categoryValidator;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Category\Validator\Category
     */
    protected $validator;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializeJson;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
     */
    protected $urlRewriteHandler;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer
     */
    protected $urlRewriteBunchReplacer;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var array
     */
    protected $validatedCategoryIds = [];

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * Category constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Bss\CategoriesImportExport\Model\ResourceModel\Import $import
     * @param Category\Validator\Category $categoryValidator
     * @param \Magento\CatalogImportExport\Model\Import\Product $importProductModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param Category\Validator\Category $validator
     * @param \Magento\Framework\Serialize\Serializer\Json $serializeJson
     * @param \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler $urlRewriteHandler
     * @param \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Bss\CategoriesImportExport\Model\ResourceModel\Import $import,
        BssImport\Category\Validator\Category $categoryValidator,
        \Magento\CatalogImportExport\Model\Import\Product $importProductModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        BssImport\Category\Validator\Category $validator,
        \Magento\Framework\Serialize\Serializer\Json $serializeJson,
        \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler $urlRewriteHandler,
        \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource;
        $this->errorAggregator = $errorAggregator;
        $this->import = $import;
        $this->categoryValidator = $categoryValidator;
        $this->importProductModel = $importProductModel;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->validator = $validator;
        $this->serializeJson = $serializeJson;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->scopeConfig = $scopeConfig;
        $this->productMetadata = $productMetadata;
        $this->filter = $filter;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->readAdapter = $this->_connection->getConnection('core_read');
        $this->writeAdapter = $this->_connection->getConnection('core_write');
        $this->moduleList = $moduleList;
    }

    /**
     * Get entity type code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'bss_category';
    }

    /**
     * Validate row
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateRow(array $rowData, $rowNum)
    {
        try {
            if (!isset($rowData['store_id'])) {
                $this->addRowError(self::ERROR_EMPTY_STORE_ID, $rowNum);
                return false;
            }
            $storeId = isset($rowData['store_id']) ? 0 : $rowData['store_id'];
            if (!$this->validator->checkEmptyCategoryName($rowData['name'], $storeId)) {
                $this->addRowError(self::ERROR_EMPTY_CATEGORY_NAME, $rowNum);
                return false;
            }
            if (!$this->validator->checkEmptyParentId($rowData['parent_id'])) {
                $this->addRowError(self::ERROR_EMPTY_PARENT_ID, $rowNum);
                return false;
            }
            $existParentCate = $this->import->checkExistCategory($rowData['parent_id']);
            if (!$existParentCate) {
                // check parent id is new (case: category belongs to new category)
                // search new category_id of parent is exist on fetched rows
                if (!$this->checkParentCategoryIdExistOnFile($rowData['parent_id'])) {
                    $this->addRowError(self::ERROR_PARENT_ID_NOT_EXIST, $rowNum);
                    return false;
                }
            } else {
                if ($rowData['path'] != '' && $existParentCate['path'] != $rowData['path']) {
                    $this->addRowError(self::ERROR_PATH, $rowNum);
                    return false;
                }
            }

            if ($rowData['category_id'] && $rowData['category_id'] <= 1) {
                $this->addRowError(self::ERROR_CATEGORY_ID_NOT_EXIST, $rowNum);
                return false;
            } else {
                $this->validatedCategoryIds[$rowData['category_id']] = $rowData['category_id'];
            }
        } catch (\Exception $exception) {
            $this->addRowError(__('Check required columns and try again!'), $rowNum);
            return false;
        }

        return true;
    }

    /**
     * @param int $parentId
     * @return bool
     */
    protected function checkParentCategoryIdExistOnFile($parentId)
    {
        return isset($this->validatedCategoryIds[$parentId]);
    }

    /**
     * Import data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteCategories();
        } elseif (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceCategories();
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveCategories();
        }

        return true;
    }

    /**
     * Save categories
     *
     * @return void
     */
    protected function saveCategories()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (array_key_exists('custom_design_from', $rowData)) {
                    if (!$this->validator->validateCustomDesignDate($rowData['custom_design_from']) ||
                        !$this->validator->validateCustomDesignDate($rowData['custom_design_to'])) {
                        $this->addRowError(
                            self::ERROR_WRONG_DATE_FORMAT,
                            $rowNum,
                            null,
                            null,
                            ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                        );
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                }
                $this->processData($rowData, $rowNum);
            }
        }
    }

    /**
     * @return array
     */
    protected function getRootCateList()
    {
        $rootCateList = $this->rootCateList;
        if (empty($rootCateList)) {
            $select = $this->readAdapter->select()
                ->from(
                    [$this->getTableName('catalog_category_entity')],
                    ['entity_id']
                )
                ->orWhere("level = ?", 1);
            $this->rootCateList = $this->readAdapter->fetchCol($select);
        }
        return $this->rootCateList;
    }

    /**
     * Process data
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function processData($rowData, $rowNum)
    {
        if ($rowData['path'] == '') {
            $existParentCate = $this->import->checkExistCategory($rowData['parent_id']);
            $rowData['path'] = $existParentCate['path'];
        }
        $category = $this->categoryFactory->create();
        $category->isObjectNew(true);
        $rowData['store_id'] = empty($rowData['store_id']) ? 0 : $rowData['store_id'];
        $issetCate = false;
        $category->setStoreId($rowData['store_id']);
        if ($rowData['category_id'] && $this->import->checkExistCategory($rowData['category_id'])) {
            $issetCate = true;
            $category->load($rowData['category_id']);
            $subCateList = $category->getChildrenCategories();
        }
        if (array_key_exists('available_sort_by', $rowData)) {
            $availableSortBy = explode($this->getMultipleValueSeparator(), $rowData['available_sort_by']);
            $category->setAvailableSortBy($availableSortBy);
        }

        // if it's Replace behavior, check category url_key - store_id pair existed
        if (Import::BEHAVIOR_REPLACE == $this->getBehavior() && isset($rowData['url_key'])) {
            if ($this->import->isCategoryUrlKeyExistedWithStore($rowData['url_key'], $rowData['store_id'])) {
                return true;
            }
        }

        $parentId = $rowData['parent_id'];
        $rootCateList = $this->getRootCateList();
        if (($rowData['category_id'] == '' && $parentId && $parentId !== '' && $parentId > 0)
            || ($rowData['category_id'] != '' && $rowData['category_id'] > 1)
        ) {
            if (!in_array($rowData['category_id'], $rootCateList)) {
                $parentCate = $this->import->checkExistCategory($parentId);
                if (empty($parentCate)) {
                    $isRealParentIdExist = false;
                    // case: new category belongs to a new category has virtual category_id, find it's real parent_id
                    foreach ($this->newCategoryIds as $key => $value) {
                        if ($key == $parentId) {
                            $parentId = $value;
                            $parentCate = $this->import->checkExistCategory($parentId);
                            $isRealParentIdExist = true;
                            break;
                        }
                    }
                    if (!$isRealParentIdExist) {
                        $this->addRowError(
                            self::ERROR_PARENT_ID_NOT_EXIST,
                            $rowNum,
                            null,
                            null,
                            ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                        );
                        return false;
                    }
                }
            }

            if (!$category->getId()) {
                if (!array_key_exists('url_key', $rowData) || $rowData['url_key'] == '') {
                    $rowData['url_key'] = $rowData['name'];
                }
            } else {
                if (array_key_exists('url_key', $rowData) && $rowData['url_key'] == '') {
                    unset($rowData['url_key']);
                }
            }

            $isUrlKeyChange = false;
            if (isset($rowData['url_key']) && $issetCate && $category->getUrlKey() != $rowData['url_key']) {
                $isUrlKeyChange = true;
            }

            foreach ($this->validColumnNames as $attributeKey) {
                if ($this->isExistAttributesKey($attributeKey, $rowData)) {
                    $category->setData($attributeKey, $rowData[$attributeKey]);
                }
            }
            $category->setParentId($parentId);
            if (!empty($parentCate)) {
                $rowData['path'] = $parentCate['path'];
            }
            if (isset($rowData['url_key'])) {
                $rowData['url_key'] = $this->filter->translitUrl($rowData['url_key']);
                $category->setUrlKey($rowData['url_key']);
            }
            if (array_key_exists('image', $rowData)) {
                $category->setData('image', [['name' => $rowData['image']]]);
            }
            $category->setCustomAttributes($this->getImportData($rowData));
            try {
                $level = count(explode("/", $category->getPath()));
                if (!$issetCate &&
                    $this->import->isCategoryUrlKeyExistedInSameLevel($category->getUrlKey(), $level, $parentId)) {
                    // case add new categories, validate for url key existed in same level
                    $this->addRowError(
                        self::ERROR_EXIST_URL_KEY,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    return false;
                } elseif ($issetCate && $isUrlKeyChange &&
                    $this->import->isCategoryUrlKeyExistedInSameLevel($category->getUrlKey(), $level, $parentId)) {
                    // case update categories, checking for url key has changed, then validate
                    $this->addRowError(
                        self::ERROR_EXIST_URL_KEY,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    return false;
                }

                $category->save();
                if ($issetCate) {
                    $this->updateCategoryPathAndUrlRewrite($category, $subCateList);

                    $this->countItemsUpdated++;
                } else {
                    $this->countItemsCreated++;
                }
            } catch (\Exception $e) {
                $this->addRowError(
                    $e->getMessage(),
                    $rowNum,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
                return false;
            }

            $id = $category->getId();
            if ($rowData['category_id'] != "" && empty($this->import->checkExistCategory($rowData['category_id']))) {
                $this->newCategoryIds[$rowData['category_id']] = $id;
            }

            $path = $this->import->getPath($parentId) . "/$id";
            $path = ltrim($path, "/");
            $level = count(explode("/", $path)) - 1;
            $this->import->setCategoryPath($id, $path, $level);
            $productSkus = [];

            if (array_key_exists('category_products', $rowData)) {
                if (!empty($rowData['category_products'])){
                    $productSkus = explode($this->getMultipleValueSeparator(), $rowData['category_products']);
                }
            }

            if (!empty($productSkus)) {
                $this->addCategory($productSkus, $parentId, $id);
            }

            if ($this->isCategoryRewritesEnabled()) {
                /**
                 * if is new category, generate url rewrite for all store
                 * if category has changed parent id, generate url rewrite for all store
                 */
                if (!$issetCate || ($issetCate && $category->dataHasChangedFor('parent_id'))) {
                    $category->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
                }
                $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
                $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);
            }
        } else {
            if ($parentId == '' && !in_array($rowData['category_id'], $rootCateList)) {
                $this->addRowError(
                    self::ERROR_EMPTY_PARENT_ID,
                    $rowNum,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
            }
            if ($parentId == 0 && !in_array($rowData['category_id'], $rootCateList)) {
                $this->addRowError(
                    self::ERROR_PARENT_ID_NOT_EXIST,
                    $rowNum,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
            }
            if ($rowData['category_id'] <= 1) {
                $this->addRowError(
                    self::ERROR_CATEGORY_ID_NOT_EXIST,
                    $rowNum,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $mainCategory
     * @param [] $subCategories
     */
    protected function updateCategoryPathAndUrlRewrite($mainCategory, $subCategories)
    {
        // set category path
        if ($subCategories) {
            foreach ($subCategories as $subCategory) {
                $subCategory->setPath(
                    $mainCategory->getPath() . '/' . $mainCategory->getId() . '/' . $subCategory->getId()
                )->save();
            }
        }

        // re-generate category url rewrite
        $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($mainCategory, true);
        $this->urlRewriteHandler->deleteCategoryRewritesForChildren($mainCategory);
        $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);
    }

    /**
     * Check remaining row data key
     *
     * @param string $urlKey
     * @param array $rowData
     * @return bool
     */
    protected function isExistAttributesKey($urlKey, $rowData)
    {
        if (array_key_exists($urlKey, $rowData)) {
            return true;
        }
        return false;
    }

    /**
     * Add category
     *
     * @param array $productSkus
     * @param int $parentId
     * @param int $id
     */
    protected function addCategory($productSkus, $parentId, $id)
    {
        if (!empty($productSkus) && $parentId !== '0') {
            $this->import->assignProductToCategory($productSkus, $id);
        }
    }

    /**
     * Get import data
     *
     * @param array $rowData
     * @return array
     */
    protected function getImportData($rowData)
    {
        $importData = [];
        foreach ($this->validColumnNames as $attributeKey) {
            if ($this->isExistAttributesKey($attributeKey, $rowData)) {
                $importData[$attributeKey] = $rowData[$attributeKey];
            }
        }
        return $importData;
    }

    /**
     * Delete and save category again
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function replaceCategories()
    {
        $this->deleteCategories();
        $this->saveCategories();
    }

    /**
     * Delete category by id
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function deleteCategories()
    {
        $categoryIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowData) {
                $categoryIds[] = $rowData['category_id'];
            }
        }
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addFieldToSelect("*")->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        $this->countItemsDeleted += count($categoryCollection);
        $categoryCollection->delete();
    }

    /**
     * Multiple value separator
     *
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }

    /**
     * Validate data rows and save bunches to DB.
     *
     * @return $this|Import\Entity\AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();
        $skuSet = [];

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->serializeJson->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    if (array_key_exists('sku', $rowData)) {
                        $skuSet[$rowData['sku']] = true;
                    }
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    if ($rowData['category_id']) {
                        $this->rowSuccess[] = $rowData['category_id'];
                    }
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $this->isBunchSizeExceeded($bunchSize, $bunchRows);

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }

        if (!empty($skuSet)) {
            $this->_processedEntitiesCount = count($skuSet);
        }
        return $this;
    }

    /**
     * Is bunch size exceeded
     *
     * @param int $bunchSize
     * @param array $bunchRows
     * @return bool
     */
    protected function isBunchSizeExceeded($bunchSize, $bunchRows)
    {
        $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;
        return $isBunchSizeExceeded;
    }

    /**
     * @return bool
     */
    protected function isCategoryRewritesEnabled()
    {
        if ($this->productMetadata->getVersion() >= "2.3.3") {
            return (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');
        }
        return (bool)$this->scopeConfig->getValue('catalog/seo/product_use_categories');
    }

    /**
     * Get Table Name
     *
     * @param String $entity
     * @return bool|mixed
     */
    public function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->_connection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $moduleInfo = $this->moduleList->getOne("Bss_CategoriesImportExport");
        return $moduleInfo['setup_version'];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            "image_import" => false
        ];
    }
}
