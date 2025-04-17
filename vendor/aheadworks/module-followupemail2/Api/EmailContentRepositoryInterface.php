<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface EmailContentRepositoryInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface EmailContentRepositoryInterface
{
    /**
     * Save email content
     *
     * @param EmailContentInterface $emailContent
     * @return EmailContentInterface
     * @throws LocalizedException If validation fails
     */
    public function save(EmailContentInterface $emailContent);

    /**
     * Retrieve email content
     *
     * @param int $contentId
     * @return EmailContentInterface
     * @throws NoSuchEntityException If email content does not exist
     */
    public function get($contentId);

    /**
     * Retrieve emails content matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EmailContentSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete email content
     *
     * @param EmailContentInterface $emailContent
     * @return bool true on success
     * @throws NoSuchEntityException If email content does not exist
     */
    public function delete(EmailContentInterface $emailContent);

    /**
     * Delete mail content by id
     *
     * @param int $contentId
     * @return bool true on success
     * @throws NoSuchEntityException If mail content does not exist
     */
    public function deleteById($contentId);
}
