<?php

namespace Amasty\Affiliate\Api;

/**
 * Interface LinksRepositoryInterface
 * @api
 */
interface LinksRepositoryInterface
{
    /**
     * @param \Amasty\Affiliate\Api\Data\LinksInterface $account
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\LinksInterface $account);

    /**
     * @param int $accountId
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($accountId);

    /**
     * @param \Amasty\Affiliate\Api\Data\LinksInterface $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\LinksInterface $account);

    /**
     * @param int $accountId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($accountId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
