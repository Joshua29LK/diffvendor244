<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\CommissionCalculation;

use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;

class GetFilteredOrderItems
{
    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    public function __construct(
        FiltersProvider $filtersProvider
    ) {
        $this->filtersProvider = $filtersProvider;
    }

    /**
     * Retrieve valid for commission calculation order items
     *
     * @param ProgramCommissionCalculationInterface|null $commissionCalculation
     * @param array $orderItems
     *
     * @return array
     */
    public function execute(
        ProgramCommissionCalculationInterface $commissionCalculation,
        array $orderItems
    ): array {
        /** @var Filter\FilterByInterface $filter */
        foreach ($this->filtersProvider->get(FiltersProvider::FILTER_TYPE_PRODUCT) as $filter) {
            $filter->execute($commissionCalculation, $orderItems);
        }

        return $orderItems;
    }
}
