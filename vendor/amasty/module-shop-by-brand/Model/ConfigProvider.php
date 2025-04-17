<?php

declare(strict_types=1);

namespace Amasty\ShopbyBrand\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    public const DEFAULT_CATEGORY_LOGO_SIZE = 30;

    /**
     * General group settings path
     */
    public const BRAND_ATTRIBUTE_CODE = 'general/attribute_code';
    public const TOOLTIP_ENABLED = 'general/tooltip_enabled';

    /**
     * Product Page group settings path
     */
    public const DISPLAY_DESCRIPTION = 'product_page/display_description';
    public const PRODUCT_WIDTH = 'product_page/width';
    public const LOGO_HEIGHT = 'product_page/height';
    public const DISPLAY_BRAND_IMAGE = 'product_page/display_brand_image';

    /**
     * Product Listing group settings path
     */
    public const SHOW_ON_LISTING = 'product_listing_settings/show_on_listing';
    public const LISTING_BRAND_LOGO_WIDTH = 'product_listing_settings/listing_brand_logo_width';
    public const LISTING_BRAND_LOGO_HEIGHT = 'product_listing_settings/listing_brand_logo_height';

    /**
     * More From Brand group settings path
     */
    public const MORE_FROM_ENABLE = 'more_from_brand/enable';
    public const MORE_FROM_TITLE = 'more_from_brand/title';
    public const MORE_FROM_COUNT = 'more_from_brand/count';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby_brand/';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $allBrandAttributeCodes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($scopeConfig);
        $this->storeManager = $storeManager;
    }

    public function getBrandAttributeCode(?int $storeId = null): string
    {
        //should be scope config because of BTS-10415
        return (string) $this->scopeConfig->getValue(
            $this->pathPrefix . self::BRAND_ATTRIBUTE_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAllBrandAttributeCodes(): array
    {
        if ($this->allBrandAttributeCodes === null) {
            $attributes = [];
            foreach ($this->storeManager->getStores() as $store) {
                $code = $this->getBrandAttributeCode((int) $store->getId());
                if ($code) {
                    $attributes[$store->getId()] = $code;
                }
            }

            $this->allBrandAttributeCodes = array_unique($attributes);
        }

        return $this->allBrandAttributeCodes;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowOnListing(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::SHOW_ON_LISTING, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getListingBrandLogoWidth(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LISTING_BRAND_LOGO_WIDTH, $storeId) ?: self::DEFAULT_CATEGORY_LOGO_SIZE;
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getListingBrandLogoHeight(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LISTING_BRAND_LOGO_HEIGHT, $storeId) ?: self::DEFAULT_CATEGORY_LOGO_SIZE;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDisplayBrandImage(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_BRAND_IMAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     * @see \Amasty\ShopbyBrand\Model\Source\Tooltip
     */
    public function getTooltipEnabled(?int $storeId = null): array
    {
        return explode(',', (string) $this->getValue(self::TOOLTIP_ENABLED, $storeId));
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDisplayDescription(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_DESCRIPTION, $storeId);
    }

    /**
     * Brand Logo Width for product.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getLogoWidth(?int $storeId = null): int
    {
        return (int) $this->getValue(self::PRODUCT_WIDTH, $storeId);
    }

    /**
     * Brand Logo Height for product.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getLogoHeight(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LOGO_HEIGHT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isMoreFromEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::MORE_FROM_ENABLE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTitleMoreFrom(?int $storeId = null): string
    {
        return (string) $this->getValue(self::MORE_FROM_TITLE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getMoreFromProductsLimit(?int $storeId = null): int
    {
        return (int) $this->getValue(self::MORE_FROM_COUNT, $storeId);
    }
}
