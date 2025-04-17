<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EmailContentSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EmailContentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get email content list
     *
     * @return EmailContentInterface[]
     */
    public function getItems();

    /**
     * Set email content list
     *
     * @param EmailContentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
