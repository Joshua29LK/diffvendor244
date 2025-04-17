<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Campaign CRUD interface
 * @api
 */
interface CampaignRepositoryInterface
{
    /**
     * Save campaign
     *
     * @param CampaignInterface $campaign
     * @return CampaignInterface
     * @throws LocalizedException If validation fails
     */
    public function save(CampaignInterface $campaign);

    /**
     * Retrieve campaign
     *
     * @param int $campaignId
     * @return CampaignInterface
     * @throws NoSuchEntityException If campaign does not exist
     */
    public function get($campaignId);

    /**
     * Retrieve campaigns matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CampaignSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete campaign
     *
     * @param CampaignInterface $campaign
     * @return bool true on success
     * @throws NoSuchEntityException If campaign does not exist
     */
    public function delete(CampaignInterface $campaign);

    /**
     * Delete campaign by id
     *
     * @param int $campaignId
     * @return bool true on success
     * @throws NoSuchEntityException If campaign does not exist
     */
    public function deleteById($campaignId);
}
