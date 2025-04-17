<?php
namespace Aheadworks\Followupemail2\Api;

/**
 * Interface StatisticsManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 * @ap
 */
interface StatisticsManagementInterface
{
    /**
     * Add new statistics history
     *
     * @param string $email
     * @param int $emailContentId
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface|null
     */
    public function addNew($email, $emailContentId);

    /**
     * Add sent email
     *
     * @param int $statId
     * @param string $email
     * @return bool
     */
    public function addSent($statId, $email);

    /**
     * Add opened email
     *
     * @param int $statId
     * @param string $email
     * @return bool
     */
    public function addOpened($statId, $email);

    /**
     * Add clicked email
     *
     * @param int $statId
     * @param string $email
     * @return bool
     */
    public function addClicked($statId, $email);

    /**
     * Get statistics by campaign id
     *
     * @param int $campaignId
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsInterface
     */
    public function getByCampaignId($campaignId);

    /**
     * Get statistics by event id
     *
     * @param int $eventId
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsInterface
     */
    public function getByEventId($eventId);

    /**
     * Get statistics by email id
     *
     * @param int $emailId
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsInterface
     */
    public function getByEmailId($emailId);

    /**
     * Get statistics by email content id
     *
     * @param int $emailContentId
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsInterface
     */
    public function getByEmailContentId($emailContentId);

    /**
     * Update statistics by campaign id
     *
     * @param int $campaignId
     * @return bool
     */
    public function updateByCampaignId($campaignId);

    /**
     * Update statistics by event id
     *
     * @param int $eventId
     * @return bool
     */
    public function updateByEventId($eventId);

    /**
     * Update statistics by email id
     *
     * @param int $emailId
     * @return bool
     */
    public function updateByEmailId($emailId);

    /**
     * Update statistics by email content id
     *
     * @param int $emailContentId
     * @return bool
     */
    public function updateByEmailContentId($emailContentId);

    /**
     * Reset statistics by campaign id
     *
     * @param int $campaignId
     * @return bool
     */
    public function resetByCampaignId($campaignId);

    /**
     * Reset statistics by event id
     *
     * @param int $eventId
     * @return bool
     */
    public function resetByEventId($eventId);

    /**
     * Reset statistics by email id
     *
     * @param int $emailId
     * @return bool
     */
    public function resetByEmailId($emailId);

    /**
     * Reset statistics by email content id
     *
     * @param int $emailContentId
     * @return bool
     */
    public function resetByEmailContentId($emailContentId);
}
