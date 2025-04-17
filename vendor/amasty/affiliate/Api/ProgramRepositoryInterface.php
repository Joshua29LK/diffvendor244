<?php

namespace Amasty\Affiliate\Api;

/**
 * Interface ProgramRepositoryInterface
 * @api
 */
interface ProgramRepositoryInterface
{
    /**
     * @param \Amasty\Affiliate\Api\Data\ProgramInterface $account
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\ProgramInterface $account);

    /**
     * @param int $accountId
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($accountId);

    /**
     * @param \Amasty\Affiliate\Api\Data\ProgramInterface $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\ProgramInterface $account);

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
