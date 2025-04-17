<?php

namespace Amasty\Affiliate\Ui\Component\DataProvider;

use Amasty\Affiliate\Model\ResourceModel\Transaction\Collection as TransactionsCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

class FilterPool extends \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool
{
    /**
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                switch ($filter->getField()) {
                    case 'created_at':
                        $filter->setField('main_table.created_at');
                        break;
                    case 'status':
                        $filter->setField('main_table.status');
                        break;
                    case 'increment_id':
                        $filter->setField('sales_order.increment_id');
                        break;
                    case 'gt_excl_tax':
                        $filter->setField(new \Zend_Db_Expr(TransactionsCollection::GT_EXCL_TAX_EXPR));
                        $this->applyHavingFilter($collection, $filter);
                        continue 2;
                }

                /** @var $filterApplier FilterApplierInterface*/
                if (isset($this->appliers[$filter->getConditionType()])) {
                    $filterApplier = $this->appliers[$filter->getConditionType()];
                } else {
                    $filterApplier = $this->appliers['regular'];
                }
                $filterApplier->apply($collection, $filter);
            }
        }
    }

    private function applyHavingFilter(Collection $collection, Filter $filter): void
    {
        switch ($filter->getConditionType()) {
            case "gteq":
                $collection->getSelect()->having($filter->getField() . ' >= ?', $filter->getValue());
                break;
            case "lteq":
                $collection->getSelect()->having($filter->getField() . ' <= ?', $filter->getValue());
                break;
        }
    }
}
