<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EmailSearchResultsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EmailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get email list
     *
     * @return EmailInterface[]
     */
    public function getItems();

    /**
     * Set email list
     *
     * @param EmailInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
