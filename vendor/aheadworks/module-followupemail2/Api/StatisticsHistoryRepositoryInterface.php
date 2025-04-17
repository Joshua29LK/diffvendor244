<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistorySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface StatisticsHistoryRepositoryInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface StatisticsHistoryRepositoryInterface
{
    /**
     * Save history
     *
     * @param StatisticsHistoryInterface $history
     * @return StatisticsHistoryInterface
     * @throws LocalizedException If validation fails
     */
    public function save(StatisticsHistoryInterface $history);

    /**
     * Retrieve history
     *
     * @param int $historyId
     * @return StatisticsHistoryInterface
     * @throws NoSuchEntityException If history does not exist
     */
    public function get($historyId);

    /**
     * Retrieve histories matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return StatisticsHistorySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete history
     *
     * @param StatisticsHistoryInterface $history
     * @return bool true on success
     * @throws NoSuchEntityException If history does not exist
     */
    public function delete(StatisticsHistoryInterface $history);

    /**
     * Delete history by id
     *
     * @param int $historyId
     * @return bool true on success
     * @throws NoSuchEntityException If history does not exist
     */
    public function deleteById($historyId);
}
