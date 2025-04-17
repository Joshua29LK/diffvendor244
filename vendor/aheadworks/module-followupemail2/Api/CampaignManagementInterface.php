<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;

/**
 * Interface CampaignManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface CampaignManagementInterface
{
    /**
     * Get active campaigns
     *
     * @return CampaignInterface[]
     */
    public function getActiveCampaigns();

    /**
     * Duplicate campaign events
     *
     * @param int $sourceCampaignId
     * @param int $destinationCampaignId
     * @return boolean
     */
    public function duplicateCampaignEvents($sourceCampaignId, $destinationCampaignId);

    /**
     * Get new event name
     *
     * @param int $campaignId
     * @param string $eventType
     * @return string
     */
    public function getNewEventName($campaignId, $eventType);

    /**
     * Get emails count
     *
     * @param int $campaignId
     * @return int
     */
    public function getEventsCount($campaignId);

    /**
     * Get events count
     *
     * @param int $campaignId
     * @return int
     */
    public function getEmailsCount($campaignId);
}
