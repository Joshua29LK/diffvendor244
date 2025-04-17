<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StatisticsHistorySearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface StatisticsHistorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get history list
     *
     * @return StatisticsHistoryInterface[]
     */
    public function getItems();

    /**
     * Set history list
     *
     * @param StatisticsHistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
