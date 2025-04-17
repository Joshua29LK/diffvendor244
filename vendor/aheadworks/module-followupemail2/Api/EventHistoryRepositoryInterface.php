<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * EventHistory CRUD interface
 * @api
 */
interface EventHistoryRepositoryInterface
{
    /**
     * Save event history
     *
     * @param EventHistoryInterface $eventHistory
     * @return EventHistoryInterface
     * @throws LocalizedException If validation fails
     */
    public function save(EventHistoryInterface $eventHistory);

    /**
     * Retrieve event history
     *
     * @param int $eventHistoryId
     * @return EventHistoryInterface
     * @throws NoSuchEntityException If event history does not exist
     */
    public function get($eventHistoryId);

    /**
     * Retrieve event histories matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EventHistorySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event history
     *
     * @param EventHistoryInterface $eventHistory
     * @return bool true on success
     * @throws NoSuchEntityException If event history does not exist
     */
    public function delete(EventHistoryInterface $eventHistory);

    /**
     * Delete event history by id
     *
     * @param int $eventHistoryId
     * @return bool true on success
     * @throws NoSuchEntityException If event history does not exist
     */
    public function deleteById($eventHistoryId);
}
