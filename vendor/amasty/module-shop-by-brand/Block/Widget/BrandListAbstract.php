<?php

namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;
use Amasty\ShopbyBrand\Model\Attribute;
use Amasty\ShopbyBrand\Model\Brand\BrandListDataProvider;
use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\Option;

abstract class BrandListAbstract extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    public const PATH_BRAND_ATTRIBUTE_CODE = 'amshopby_brand/general/attribute_code';

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var UrlBuilderInterface
     */
    private $amUrlBuilder;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var BrandListDataProvider
     */
    protected $brandListDataProvider;

    /**
     * @var Attribute
     */
    private $brandAttribute;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        DataHelper $helper,
        UrlBuilderInterface $amUrlBuilder,
        BrandListDataProvider $brandListDataProvider,
        Attribute $brandAttribute,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->amUrlBuilder = $amUrlBuilder;
        $this->dataPersistor = $dataPersistor;
        $this->brandListDataProvider = $brandListDataProvider;
        $this->brandAttribute = $brandAttribute;

        parent::__construct($context, $data);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 86400);
        }
    }

    protected function getCacheTags(): array
    {
        $tags = parent::getCacheTags();
        $tags[] = OptionSetting::CACHE_TAG;

        return $tags;
    }

    public function getIdentities(): array
    {
        $productAttribute = $this->brandAttribute->getAttribute();
        if ($productAttribute !== null) {
            return $productAttribute->getIdentities();
        }

        return [];
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @return string
     */
    public function getBrandUrl(Option $option)
    {
        return $this->amUrlBuilder->getUrl('ambrand/index/index', ['id' => $option->getValue()]);
    }

    /**
     * @return DataPersistorInterface
     */
    public function getDataPersistor(): DataPersistorInterface
    {
        return $this->dataPersistor;
    }

    protected function _beforeToHtml()
    {
        $this->initializeBlockConfiguration();

        return parent::_beforeToHtml();
    }

    /**
     * deprecated. used for back compatibility.
     */
    public function initializeBlockConfiguration(): void
    {
        $configValues = $this->_scopeConfig->getValue(
            $this->getConfigValuesPath(),
            ScopeInterface::SCOPE_STORE
        );
        foreach (($configValues ?: []) as $option => $value) {
            if ($this->getData($option) === null) {
                $this->setData($option, $value);
            }
        }
    }

    abstract protected function getConfigValuesPath(): string;

    public function isDisplayZero(): bool
    {
        return (bool) $this->getData('display_zero');
    }
}
