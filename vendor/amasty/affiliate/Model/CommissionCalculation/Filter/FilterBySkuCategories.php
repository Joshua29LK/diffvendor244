<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\CommissionCalculation\Filter;

use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;
use Amasty\Affiliate\Model\Program\Source\CommissionActionStrategy;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class FilterBySkuCategories implements FilterByInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var array
     */
    private $skus = [];

    /**
     * @var array
     */
    private $categories = [];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * @param ProgramCommissionCalculationInterface $commissionCalculation
     * @param array $orderItems
     */
    public function execute(ProgramCommissionCalculationInterface $commissionCalculation, array &$orderItems): void
    {
        $this->prepareSkusAndCategories($commissionCalculation, $orderItems);

        $productIds = array_map(function ($item) {
            return $item->getProductId();
        }, $orderItems);

        $criteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $criteriaBuilder->addFilter(
            'entity_id',
            $productIds,
            'IN'
        )->create();

        foreach ($this->productRepository->getList($searchCriteria)->getItems() as $product) {
            if (!$this->isItemValid($product, $commissionCalculation)) {
                $this->unsetOrderItemByProductId($orderItems, (int)$product->getId());
            }
        }
    }

    /**
     * @param ProgramCommissionCalculationInterface $commissionCalculation
     * @param array $orderItems
     */
    private function prepareSkusAndCategories(
        ProgramCommissionCalculationInterface $commissionCalculation,
        array $orderItems
    ): void {
        $skus = $commissionCalculation->getSkus();
        $categories = $commissionCalculation->getCategories();

        //must process all child's of bundle\configurable products for proper calculation
        foreach ($orderItems as $item) {
            $childrenItems = $item->getChildrenItems();
            if (!empty($childrenItems)) {
                $parentKey = array_search($item->getProduct()->getSku(), $skus);
                if ($parentKey === false) {
                    continue;
                }

                foreach ($childrenItems as $childrenItem) {
                    $skus[] = $childrenItem->getSku();
                }
            }
        }

        $this->skus = array_unique($skus);
        $this->categories = $categories;
    }

    /**
     * @param array $orderItems
     * @param int $productId
     */
    private function unsetOrderItemByProductId(array &$orderItems, int $productId): void
    {
        foreach ($orderItems as $key => $item) {
            if ($item->getProductId() == $productId) {
                unset($orderItems[$key]);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param ProgramCommissionCalculationInterface $commissionCalculation
     *
     * @return bool
     */
    private function isItemValid(
        ProductInterface $product,
        ProgramCommissionCalculationInterface $commissionCalculation
    ): bool {
        if ($this->skus || $this->categories) {
            $result = in_array($product->getSku(), $this->skus)
                || !empty(array_intersect($product->getCategoryIds(), $this->categories));

            if ($commissionCalculation->getActionStrategy() === CommissionActionStrategy::EXCLUDE) {
                $result = !$result;
            }
        } else {
            $result = true;
        }

        return $result;
    }
}
