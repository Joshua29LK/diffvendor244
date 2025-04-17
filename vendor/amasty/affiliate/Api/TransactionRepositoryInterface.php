<?php

namespace Amasty\Affiliate\Api;

/**
 * Interface TransactionRepositoryInterface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * @param \Amasty\Affiliate\Api\Data\TransactionInterface $account
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\TransactionInterface $account);

    /**
     * @param int $withdrawalId
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($withdrawalId);

    /**
     * @param string $orderIncrementId
     * @param string $programId
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderProgramIds($orderIncrementId, $programId);

    /**
     * @param \Amasty\Affiliate\Api\Data\TransactionInterface $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\TransactionInterface $account);

    /**
     * @param int $withdrawalId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($withdrawalId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
