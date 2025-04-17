<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\CommissionCalculation;

class FiltersProvider
{
    public const FILTER_TYPE_PRODUCT = 'product';

    /**
     * @var array
     */
    private $filters;

    public function __construct(
        array $filters = []
    ) {
        $this->setFilters($filters);
    }

    /**
     * Get list of filters by type
     *
     * @param string $type
     *
     * @return array
     */
    public function get(string $type): array
    {
        $filters = [];

        if (isset($this->filters[$type])) {
            $filters = $this->filters[$type];
        }

        return $filters;
    }

    /**
     * @param array $filters
     */
    private function setFilters(array $filters): void
    {
        foreach ($filters as $filterType) {
            foreach ($filterType as $filter) {
                if (!$filter instanceof Filter\FilterByInterface) {
                    throw new \InvalidArgumentException(
                        sprintf('Filter must implement %s', Filter\FilterByInterface::class)
                    );
                }
            }
        }
        $this->filters = $filters;
    }
}
