<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Validator
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class Validator
{
    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param DateTime $dateTime
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        DateTime $dateTime
    ) {
        $this->campaignManagement = $campaignManagement;
        $this->dateTime = $dateTime;
    }

    /**
     * @param EventInterface $event
     * @return bool
     */
    public function isEventValid($event)
    {
        if ($event->getStatus() == EventInterface::STATUS_ENABLED) {
            $eventCampaignId = $event->getCampaignId();
            /** @var CampaignInterface[] $activeCampaigns */
            $activeCampaigns = $this->campaignManagement->getActiveCampaigns();
            foreach ($activeCampaigns as $activeCampaign) {
                if ($activeCampaign->getId() == $eventCampaignId) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is email valid to send
     *
     * @param EmailInterface $email
     * @param string $lastSentDate
     * @return bool
     */
    public function isEmailValidToSend($email, $lastSentDate)
    {
        if ($email->getWhen() == EmailInterface::WHEN_BEFORE) {
            $sendTimestamp = $this->dateTime->timestamp($lastSentDate);
        } else {
            $sendTimestamp = $this->dateTime->timestamp($lastSentDate) + $this->getDeltaTimestamp($email);
        }
        $currentTimestamp = $this->dateTime->timestamp();

        return $sendTimestamp <= $currentTimestamp;
    }

    /**
     * Get delta timestamp
     * @param EmailInterface $email
     * @return int
     */
    private function getDeltaTimestamp($email)
    {
        return 60 * ($email->getEmailSendMinutes() +
                60 * ($email->getEmailSendHours() + $email->getEmailSendDays() * 24));
    }
}
