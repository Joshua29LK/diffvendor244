<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EventHistorySearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventHistorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get event history list
     *
     * @return EventHistoryInterface[]
     */
    public function getItems();

    /**
     * Set event history list
     *
     * @param EventHistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
