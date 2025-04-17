<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class FilterResolver
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        LoggerInterface $logger,
        Config $eavConfig
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
    }

    public function getFilterSetting(AttributeInterface $entity): ?FilterSettingInterface
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $filterSetting = $extensionAttributes->getFilterSetting();
        if (!$filterSetting) {
            try {
                $filterSetting = $this->filterSettingRepository->getByAttributeCode($entity->getAttributeCode());
                $extensionAttributes->setFilterSetting($filterSetting);
                $entity->setExtensionAttributes($extensionAttributes);
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
            }
        }

        return $filterSetting;
    }

    public function getFilterSettingByCode(?string $code): ?FilterSettingInterface
    {
        return  $this->getFilterSetting($this->eavConfig->getAttribute(Product::ENTITY, $code));
    }
}
