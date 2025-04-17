<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * EventQueue CRUD interface
 * @api
 */
interface EventQueueRepositoryInterface
{
    /**
     * Save event queue
     *
     * @param EventQueueInterface $eventQueue
     * @return EventQueueInterface
     * @throws LocalizedException If validation fails
     */
    public function save(EventQueueInterface $eventQueue);

    /**
     * Retrieve event queue
     *
     * @param int $eventQueueId
     * @return EventQueueInterface
     * @throws NoSuchEntityException If event queue does not exist
     */
    public function get($eventQueueId);

    /**
     * Retrieve event queue items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EventQueueSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event queue
     *
     * @param EventQueueInterface $eventQueue
     * @return bool true on success
     * @throws NoSuchEntityException If event queue does not exist
     */
    public function delete(EventQueueInterface $eventQueue);

    /**
     * Delete event queue by id
     *
     * @param int $eventQueueId
     * @return bool true on success
     * @throws NoSuchEntityException If event queue does not exist
     */
    public function deleteById($eventQueueId);
}
