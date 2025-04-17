<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Program\Handlers;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Amasty\Affiliate\Model\ResourceModel\ProgramCommissionCalculation as ProgramCommissionCalculationResource;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var ProgramCommissionCalculationResource
     */
    private $commissionCalculationResource;

    public function __construct(
        ProgramCommissionCalculationResource $commissionCalculationResource
    ) {
        $this->commissionCalculationResource = $commissionCalculationResource;
    }

    /**
     * @param ProgramInterface $entity
     * @param array $arguments
     *
     * @return bool|object
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($entity, $arguments = [])
    {
        if ($commissionCalc = $entity->getCommissionCalculation()) {
            $commissionCalc->setProgramId($entity->getProgramId());
            $this->commissionCalculationResource->save($commissionCalc);
        }

        return $entity;
    }
}
