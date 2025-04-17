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
namespace Bss\CategoriesImportExport\Model\Export;

use Bss\CategoriesImportExport\Block\Adminhtml\Export\Filter\Form as FilterForm;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Model\Export as ExportModel;

/**
 * Class Category
 * @package Bss\CategoriesImportExport\Model\Export
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\CategoriesImportExport\Model\ResourceModel\Export
     */
    protected $export;

    /**
     * @var string
     */
    protected $varDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * Category constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ExportModel\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\CategoriesImportExport\Model\ResourceModel\Export $export
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\File\Csv $csv
     * @param FileFactory $fileFactory
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\CategoriesImportExport\Model\ResourceModel\Export $export,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\File\Csv $csv,
        FileFactory $fileFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->request = $request;
        $this->export = $export;
        $this->filesystem = $filesystem;
        $this->io = $io;
        $this->csv = $csv;
        $this->fileFactory = $fileFactory;
        $this->moduleList = $moduleList;
    }

    /**
     * Export process
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        $fileName = $this->getFileName();
        $filterData = $this->request->getParam(ExportModel::FILTER_ELEMENT_GROUP);

        if (!isset($filterData['export-related-skus'])) {
                       $filterData['export-related-skus'] = '1';
        }
        if (!isset($filterData['store-id'])) {
            $filterData['store-id'] = 'all';
        }
        if (!isset($filterData['category-id'])) {
            $filterData['category-id'] = '';
        }
        if (!isset($filterData['export-by'])) {
            if (isset($filterData['store-id']) &&
                                $filterData['store-id'] != 'all') {
                     $filterData['export-by'] = 'store-id';
            } elseif (isset($filterData['category-id']) &&
                                $filterData['category-id'] &&
                                $filterData['category-id'] != '') {
                $filterData['export-by'] = 'category-id';
            } else {
                $filterData['export-by'] = 'all';
            }
        }

        if (!$fileName) {
            $currentDate = date('Ymd_His');
            $fileName = $this->getEntityTypeCode() . $currentDate . $this->getWriter()->getFileExtension();
        }

        $this->varDirectory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $dir = $this->varDirectory->getAbsolutePath('bss/export');
        $this->io->mkdir($dir, 0775);
        $outputFile = $dir . "/" . $fileName;
        $categories = $this->export->getCategories($filterData);
        $data = $this->export->getExportData($categories, $filterData);
        $this->csv->saveData($outputFile, $data);

        return [
            'type'  => "filename",
            'value' => "bss/export/" . $fileName,
            'rm'    => true,
        ];
    }

    /**
     * @return string
     */
    public function getFilterFormBlock()
    {
        return FilterForm::class;
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
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @inheritdoc
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * Entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'bss_category';
    }

    /**
     * Get header columns
     *
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        // TODO: Implement _getHeaderColumns() method.
    }

    /**
     * Get entity collection
     *
     * @inheritdoc
     */
    protected function _getEntityCollection()
    {
        // TODO: Implement _getEntityCollection() method.
    }
}
