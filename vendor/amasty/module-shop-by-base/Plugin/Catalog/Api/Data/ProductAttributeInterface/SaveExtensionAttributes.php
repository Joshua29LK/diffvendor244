<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Plugin\Catalog\Api\Data\ProductAttributeInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class SaveExtensionAttributes
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(FilterSettingRepositoryInterface $filterSettingRepository)
    {
        $this->filterSettingRepository = $filterSettingRepository;
    }

    public function afterSave(
        ProductAttributeInterface $entity,
        ProductAttributeInterface $result
    ): ProductAttributeInterface {
        $extensionAttributes = $entity->getExtensionAttributes();
        $filterSetting = $extensionAttributes->getFilterSetting();
        if ($filterSetting) {
            $this->filterSettingRepository->save($filterSetting);

            $resultExtentionAttributes = $result->getExtensionAttributes();
            $resultExtentionAttributes->setFilterSetting($filterSetting);
            $result->setExtensionAttributes($resultExtentionAttributes);
        }

        return $result;
    }
}
