<?php

namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting as OptionSettingModel;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\Helper\Context;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;

class OptionSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var  Repository
     */
    private $repository;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    public function __construct(
        Context $context,
        OptionSettingRepositoryInterface $optionSettingRepository,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->optionSettingRepository = $optionSettingRepository;
    }

    /**
     * @param string $value
     * @param string $filterCode
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getSettingByValue($value, $filterCode, $storeId)
    {
        $filterCode = $this->validateFilterCode($filterCode);
        /** @var OptionSettingModel $setting */
        $setting = $this->optionSettingRepository->getByParams($filterCode, $value, $storeId);

        if (!$setting->getId()) {
            $setting->setFilterCode($filterCode);
            $attribute = $this->getAttribute(substr($filterCode, 5), $storeId);
            $setting = $this->applyDataFromOption($attribute, $value, $setting);
        }

        return $setting;
    }

    /**
     * deprecated. remove after move from filter code to attribute code into filter options
     */
    private function validateFilterCode(string $filterCode): string
    {
        if (strpos($filterCode, FilterSetting::ATTR_PREFIX) === false) {
            $filterCode = FilterSetting::ATTR_PREFIX . $filterCode;
        }

        return $filterCode;
    }

    /**
     * @param string $attributeCode
     * @param int $storeId
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface|\Magento\Eav\Api\Data\AttributeInterface
     */
    public function getAttribute($attributeCode, $storeId)
    {
        $attribute = $this->repository->get($attributeCode);
        $attribute->setStoreId($storeId);

        return $attribute;
    }

    /**
     * @param $attribute
     * @param $value
     * @param OptionSettingInterface $setting
     *
     * @return OptionSettingInterface
     */
    public function applyDataFromOption($attribute, $value, OptionSettingInterface $setting)
    {
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue() == $value) {
                $this->initiateSettingByOption($setting, $option);
                break;
            }
        }

        return $setting;
    }

    /**
     * @param OptionSettingInterface $setting
     * @param AttributeOptionInterface $option
     * @return $this
     */
    protected function initiateSettingByOption(
        OptionSettingInterface $setting,
        AttributeOptionInterface $option
    ) {
        $setting->setValue($option->getValue());
        $setting->setTitle($option->getLabel());
        $setting->setMetaTitle($option->getLabel());
        return $this;
    }
}
