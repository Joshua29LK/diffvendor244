<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EventSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get event list
     *
     * @return EventInterface[]
     */
    public function getItems();

    /**
     * Set event list
     *
     * @param EventInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
