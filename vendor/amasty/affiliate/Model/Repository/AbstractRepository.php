<?php

namespace Amasty\Affiliate\Model\Repository;

abstract class AbstractRepository
{
    /**
     * @param $id
     * @return bool
     */
    public function deleteById($id)
    {
        $model = $this->get($id);
        $this->delete($model);

        return true;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $collection->addOrder(
                $sortOrder->getField(),
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $totalCount = $collection->getSize();
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $entities = [];
        /** @var \Magento\Eav\Api\Data\AttributeInterface $entity */
        foreach ($collection as $entity) {
            $entities[] = $entity;
        }
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($entities);
        $searchResults->setTotalCount($totalCount);
        return $searchResults;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param $collection
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        $collection
    ) {
        /** @var \Magento\Framework\Api\Search\FilterGroup $filter */
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter(
                $filter->getField(),
                [$condition => $filter->getValue()]
            );
        }
    }
}
