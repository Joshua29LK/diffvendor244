<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ProgramCommissionCalculationInterface extends ExtensibleDataInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getProgramId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setProgramId(int $id): ProgramCommissionCalculationInterface;

    /**
     * @return bool
     */
    public function getIsEnabled(): bool;

    /**
     * @param bool $isEnabled
     *
     * @return ProgramCommissionCalculationInterface
     */
    public function setIsEnabled(bool $isEnabled): ProgramCommissionCalculationInterface;

    /**
     * @return int
     */
    public function getActionStrategy(): int;

    /**
     * @param int $actionStrategy
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setActionStrategy(int $actionStrategy): ProgramCommissionCalculationInterface;

    /**
     * @return string[]
     */
    public function getSkus(): array;

    /**
     * @param string[] $skus
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setSkus(array $skus): ProgramCommissionCalculationInterface;

    /**
     * @return string[]
     */
    public function getCategories(): array;

    /**
     * @param string[] $categories
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setCategories(array $categories): ProgramCommissionCalculationInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationExtensionInterface $extensionAttributes
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface
     */
    public function setExtensionAttributes(
        \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationExtensionInterface $extensionAttributes
    ): \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;
}
