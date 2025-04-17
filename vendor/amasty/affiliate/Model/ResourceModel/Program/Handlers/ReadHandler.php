<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Program\Handlers;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Amasty\Affiliate\Model\ProgramCommissionCalculation;
use Amasty\Affiliate\Model\ResourceModel\Program\ProgramCommissionCalculation\CollectionFactory
    as CommissionCalculationCollectionFactory;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var CommissionCalculationCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CommissionCalculationCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param ProgramInterface $entity
     * @param array $arguments
     *
     * @return bool|object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getProgramId()) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(ProgramCommissionCalculation::PROGRAM_ID, $entity->getProgramId());

            if ($collection->count() > 0) {
                $entity->setCommissionCalculation($collection->getFirstItem());
            }
        }

        return $entity;
    }
}
