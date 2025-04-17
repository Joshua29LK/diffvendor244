<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Unsubscribe CRUD interface
 * @api
 */
interface UnsubscribeRepositoryInterface
{
    /**
     * Save unsubscribe item
     *
     * @param UnsubscribeInterface $unsubscribeItem
     * @return UnsubscribeInterface
     * @throws LocalizedException If validation fails
     */
    public function save(UnsubscribeInterface $unsubscribeItem);

    /**
     * Retrieve unsubscribe item
     *
     * @param int $unsubscribeItemId
     * @return UnsubscribeInterface
     * @throws NoSuchEntityException If an unsubscribe item does not exist
     */
    public function get($unsubscribeItemId);

    /**
     * Retrieve unsubscribe items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return UnsubscribeSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete unsubscribe item
     *
     * @param UnsubscribeInterface $unsubscribeItem
     * @return bool true on success
     * @throws NoSuchEntityException If an unsubscribe item does not exist
     */
    public function delete(UnsubscribeInterface $unsubscribeItem);

    /**
     * Delete  unsubscribe item by id
     *
     * @param int $unsubscribeItemId
     * @return bool true on success
     * @throws NoSuchEntityException If an unsubscribe item does not exist
     */
    public function deleteById($unsubscribeItemId);
}
