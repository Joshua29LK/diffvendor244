<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Model\Template\FilterFactory as Followupemail2FilterFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\UrlInterface;
use Magento\Email\Model\Template\Config as TemplateConfig;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\Template\FilterFactory;

/**
 * Class Template
 * @package Aheadworks\Followupemail2\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class Template extends \Magento\Email\Model\Template
{
    /**
     * @var Followupemail2FilterFactory
     */
    private $filterFactory;

    /**
     * @param Context $context
     * @param DesignInterface $design
     * @param Registry $registry
     * @param AppEmulation $appEmulation
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepo
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $scopeConfig
     * @param TemplateConfig $emailConfig
     * @param TemplateFactory $templateFactory
     * @param FilterManager $filterManager
     * @param UrlInterface $urlModel
     * @param FilterFactory $filterFactory
     * @param Followupemail2FilterFactory $fue2FilterFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DesignInterface $design,
        Registry $registry,
        AppEmulation $appEmulation,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepo,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig,
        TemplateConfig $emailConfig,
        TemplateFactory $templateFactory,
        FilterManager $filterManager,
        UrlInterface $urlModel,
        FilterFactory $filterFactory,
        Followupemail2FilterFactory $fue2FilterFactory,
        array $data = []
    ) {
        $this->filterFactory = $fue2FilterFactory;
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $filterManager,
            $urlModel,
            $filterFactory,
            $data
        );
    }

    /**
     * @return Followupemail2FilterFactory
     */
    protected function getFilterFactory()
    {
        return $this->filterFactory;
    }
}
