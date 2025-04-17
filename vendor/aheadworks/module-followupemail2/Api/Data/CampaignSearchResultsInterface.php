<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CampaignSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface CampaignSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get campaign list
     *
     * @return CampaignInterface[]
     */
    public function getItems();

    /**
     * Set campaign list
     *
     * @param CampaignInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
