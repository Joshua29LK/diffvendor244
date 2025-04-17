<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\FilterDataLoader;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;

interface AdapterInterface
{
    /**
     * Method loads custom filters data and writes it to FilterSetting model
     *
     * @param FilterSettingInterface $filterSetting
     * @param string $filterCode
     * @param string|null $fieldName
     */
    public function load(FilterSettingInterface $filterSetting, string $filterCode, ?string $fieldName = null): void;

    /**
     * Method checks ability to use current data loader
     *
     * @param string $filterCode
     * @return bool
     */
    public function isApplicable(string $filterCode): bool;
}
