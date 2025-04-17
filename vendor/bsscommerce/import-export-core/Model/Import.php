<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_ImportExportCore
 *  @author    Extension Team
 *  @copyright Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ImportExportCore\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Import
 */
class Import extends \Magento\ImportExport\Model\Import
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DataHelper $importExportData
     * @param ScopeConfigInterface $coreConfig
     * @param ConfigInterface $importConfig
     * @param Factory $entityFactory
     * @param Data $importData
     * @param CsvFactory $csvFactory
     * @param FileTransferFactory $httpFactory
     * @param UploaderFactory $uploaderFactory
     * @param BehaviorFactory $behaviorFactory
     * @param IndexerRegistry $indexerRegistry
     * @param History $importHistoryModel
     * @param DateTime $localeDate
     * @param array $data
     * @param ManagerInterface|null $messageManager
     * @param Random|null $random
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        Data $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        array $data = [],
        ManagerInterface $messageManager = null,
        Random $random = null
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $logger,
            $filesystem,
            $importExportData,
            $coreConfig,
            $importConfig,
            $entityFactory,
            $importData,
            $csvFactory,
            $httpFactory,
            $uploaderFactory,
            $behaviorFactory,
            $indexerRegistry,
            $importHistoryModel,
            $localeDate,
            $data,
            $messageManager,
            $random
        );
    }

    /**
     * Remove BOM from a file
     *
     * @param string $sourceFile
     * @return $this
     * @throws FileSystemException
     */
    protected function _removeBom($sourceFile)
    {
        $string = $this->_varDirectory->readFile($this->_varDirectory->getRelativePath($sourceFile));
        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            $this->_varDirectory->writeFile($this->_varDirectory->getRelativePath($sourceFile), $string);
        }

        if (!$this->checkMagento243() && preg_match('/(\"")/i', $string)) {
            $string = str_replace('\""', '\"', $string);
            $this->_varDirectory->writeFile($this->_varDirectory->getRelativePath($sourceFile), $string);
        }
        return $this;
    }

    /**
     * Check version magento >= 243
     *
     * @retrun bool
     */
    public function checkMagento243()
    {
        $versionMagento = $this->productMetadata->getVersion();
        if ($versionMagento >= "2.4.3") {
            return true;
        }
        return false;
    }
}
