<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface QueueSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface QueueSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get queue list
     *
     * @return QueueInterface[]
     */
    public function getItems();

    /**
     * Set queue list
     *
     * @param QueueInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
