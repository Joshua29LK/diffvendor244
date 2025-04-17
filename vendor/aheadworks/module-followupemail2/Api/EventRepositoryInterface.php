<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Event CRUD interface
 * @api
 */
interface EventRepositoryInterface
{
    /**
     * Save event
     *
     * @param EventInterface $event
     * @return EventInterface
     * @throws LocalizedException If validation fails
     */
    public function save(EventInterface $event);

    /**
     * Retrieve event
     *
     * @param int $eventId
     * @return EventInterface
     * @throws NoSuchEntityException If event does not exist
     */
    public function get($eventId);

    /**
     * Retrieve events matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EventSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event
     *
     * @param EventInterface $event
     * @return bool true on success
     * @throws NoSuchEntityException If $event does not exist
     */
    public function delete(EventInterface $event);

    /**
     * Delete event by id
     *
     * @param int $eventId
     * @return bool true on success
     * @throws NoSuchEntityException If event does not exist
     */
    public function deleteById($eventId);
}
