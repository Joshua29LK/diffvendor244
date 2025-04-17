<?php

namespace Amasty\Affiliate\Api;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Model\ResourceModel\Account;

/**
 * Interface AccountRepositoryInterface
 * @api
 */
interface AccountRepositoryInterface
{
    /**
     * Is Customer have affiliate
     * @param int|null $customerId
     * @return mixed
     */
    public function isAffiliate($customerId = null);

    /**
     * @param \Amasty\Affiliate\Api\Data\AccountInterface $account
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\AccountInterface $account);

    /**
     * @param int $accountId
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($accountId);

    /**
     * @param \Amasty\Affiliate\Api\Data\AccountInterface $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\AccountInterface $account);

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

    /**
     * @param int $customerId
     * @return AccountInterface
     */
    public function getByCustomerId($customerId);

    /**
     * @param string $couponCode
     * @return AccountInterface
     */
    public function getByCouponCode($couponCode);

    /**
     * @param string $code
     * @return AccountInterface
     */
    public function getByReferringCode($code);
}
