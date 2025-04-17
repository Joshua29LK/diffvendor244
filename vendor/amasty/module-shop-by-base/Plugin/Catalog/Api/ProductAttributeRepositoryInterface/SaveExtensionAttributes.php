<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Plugin\Catalog\Api\ProductAttributeRepositoryInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

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
        ProductAttributeRepositoryInterface $subject,
        ProductAttributeInterface $result,
        ProductAttributeInterface $entity
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
