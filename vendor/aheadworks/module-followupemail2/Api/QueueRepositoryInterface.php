<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Queue CRUD interface
 * @api
 */
interface QueueRepositoryInterface
{
    /**
     * Save queue item
     *
     * @param QueueInterface $queueItem
     * @return QueueInterface
     * @throws LocalizedException If validation fails
     */
    public function save(QueueInterface $queueItem);

    /**
     * Retrieve queue item
     *
     * @param int $queueItemId
     * @return QueueInterface
     * @throws NoSuchEntityException If queue item does not exist
     */
    public function get($queueItemId);

    /**
     * Retrieve queue items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return QueueSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete queue item
     *
     * @param QueueInterface $queueItem
     * @return bool true on success
     * @throws NoSuchEntityException If queue item does not exist
     */
    public function delete(QueueInterface $queueItem);

    /**
     * Delete queue item by id
     *
     * @param int $queueItemId
     * @return bool true on success
     * @throws NoSuchEntityException If queue item does not exist
     */
    public function deleteById($queueItemId);
}
