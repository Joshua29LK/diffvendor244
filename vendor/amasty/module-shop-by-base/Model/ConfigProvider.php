<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const AMSHOPBY_ROOT_GENERAL_URL_PATH = 'general/url';
    public const AMSHOPBY_ROOT_ENABLED_PATH = 'general/enabled';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby_root/';

    /**
     * @return string
     */
    public function getAllProductsUrlKey()
    {
        return $this->getValue(self::AMSHOPBY_ROOT_GENERAL_URL_PATH);
    }

    /**
     * @return bool
     */
    public function isAllProductsEnabled(): bool
    {
        return $this->isSetFlag(self::AMSHOPBY_ROOT_ENABLED_PATH);
    }
}
