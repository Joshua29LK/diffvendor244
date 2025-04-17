<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EventQueueSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventQueueSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get event queue list
     *
     * @return EventQueueInterface[]
     */
    public function getItems();

    /**
     * Set event queue list
     *
     * @param EventQueueIInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
