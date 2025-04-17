<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface UnsubscribeSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface UnsubscribeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get unsubscribe items
     *
     * @return UnsubscribeInterface[]
     */
    public function getItems();

    /**
     * Set unsubscribe items
     *
     * @param UnsubscribeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
