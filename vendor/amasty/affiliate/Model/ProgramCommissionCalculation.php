<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ProgramCommissionCalculation extends AbstractExtensibleModel implements ProgramCommissionCalculationInterface
{
    public const ID = 'id';
    public const PROGRAM_ID = 'program_id';
    public const IS_ENABLED = 'is_enabled';
    public const ACTION_STRATEGY = 'action_strategy';
    public const SKUS = 'skus';
    public const CATEGORIES = 'categories';

    protected function _construct()
    {
        $this->_init(ResourceModel\ProgramCommissionCalculation::class);
    }

    public function getProgramId(): int
    {
        return (int)$this->_getData(self::PROGRAM_ID);
    }

    public function setProgramId(int $id): ProgramCommissionCalculationInterface
    {
        return $this->setData(self::PROGRAM_ID, $id);
    }

    public function getIsEnabled(): bool
    {
        return (bool)$this->_getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $isEnabled): ProgramCommissionCalculationInterface
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
    }

    public function getActionStrategy(): int
    {
        return (int)$this->_getData(self::ACTION_STRATEGY);
    }

    public function setActionStrategy(int $actionStrategy): ProgramCommissionCalculationInterface
    {
        return $this->setData(self::ACTION_STRATEGY, $actionStrategy);
    }

    public function getSkus(): array
    {
        return (array)$this->_getData(self::SKUS);
    }

    public function setSkus(array $skus): ProgramCommissionCalculationInterface
    {
        return $this->setData(self::SKUS, $skus);
    }

    public function getCategories(): array
    {
        return (array)$this->_getData(self::CATEGORIES);
    }

    public function setCategories(array $categories): ProgramCommissionCalculationInterface
    {
        return $this->setData(self::CATEGORIES, $categories);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationExtensionInterface $extensionAttributes
    ): \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
