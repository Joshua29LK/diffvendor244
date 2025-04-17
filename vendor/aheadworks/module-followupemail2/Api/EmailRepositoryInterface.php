<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Email CRUD interface
 * @api
 */
interface EmailRepositoryInterface
{
    /**
     * Save email
     *
     * @param EmailInterface $email
     * @return EmailInterface
     * @throws LocalizedException If validation fails
     */
    public function save(EmailInterface $email);

    /**
     * Retrieve email
     *
     * @param int $emailId
     * @return EmailInterface
     * @throws NoSuchEntityException If email does not exist
     */
    public function get($emailId);

    /**
     * Retrieve emails matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EmailSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete email
     *
     * @param EmailInterface $email
     * @return bool true on success
     * @throws NoSuchEntityException If $email does not exist
     */
    public function delete(EmailInterface $email);

    /**
     * Delete email by id
     *
     * @param int $emailId
     * @return bool true on success
     * @throws NoSuchEntityException If email does not exist
     */
    public function deleteById($emailId);
}
