<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FillAttributeCodeColumn implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * TODO Check magento function to copy data column and rewrite it (remove prefix)
     * @return DataPatchInterface
     */
    public function apply()
    {
        $filterSettingCollection = $this->collectionFactory->create();
        foreach ($filterSettingCollection as $filterSetting) {
            $code = substr($filterSetting->getFilterCode(), 0, strlen(FilterSetting::ATTR_PREFIX))
                ? substr($filterSetting->getFilterCode(), strlen(FilterSetting::ATTR_PREFIX))
                : $filterSetting->getFilterCode();
            $filterSetting->setAttributeCode($code);
        }
        $filterSettingCollection->save();

        return $this;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
