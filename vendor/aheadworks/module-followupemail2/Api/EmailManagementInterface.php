<?php
namespace Aheadworks\Followupemail2\Api;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface EmailManagementInterface
 * @package Aheadworks\Followupemail2\Api
 * @api
 */
interface EmailManagementInterface
{
    /**
     * Disable the email
     *
     * @param int $emailId
     * @return EmailInterface
     * @throws NoSuchEntityException
     */
    public function disableEmail($emailId);

    /**
     * Get emails by event id
     *
     * @param int $eventId
     * @param bool $enabledOnly
     * @return EmailInterface[]
     */
    public function getEmailsByEventId($eventId, $enabledOnly = false);

    /**
     * Get next email to send
     *
     * @param int $eventId
     * @param int $countOfSentEmails
     * @return EmailInterface|false
     */
    public function getNextEmailToSend($eventId, $countOfSentEmails);

    /**
     * Change status of the email (enabled -> disabled, disabled->enabled)
     * @param int $emailId
     * @return EmailInterface
     * @throws NoSuchEntityException
     */
    public function changeStatus($emailId);

    /**
     * Change position of the email in the event chain
     * @param int $emailId
     * @param int $position
     * @return EmailInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function changePosition($emailId, $position);

    /**
     * Is email first in the chain
     *
     * @param int|null $emailId
     * @param int $eventId
     * @return boolean
     */
    public function isFirst($emailId, $eventId);

    /**
     * Is email can be first in the chain
     *
     * @param int|null $emailId
     * @param int $eventId
     * @return boolean
     */
    public function isCanBeFirst($emailId, $eventId);

    /**
     * Get email statistics data
     *
     * @param EmailInterface $email
     * @return StatisticsInterface
     */
    public function getStatistics($email);

    /**
     * Get email content statistics data
     *
     * @param int $emailContentId
     * @return StatisticsInterface
     */
    public function getStatisticsByContentId($emailContentId);

    /**
     * Get new email position
     *
     * @param int $eventId
     * @return int
     */
    public function getNewEmailPosition($eventId);

    /**
     * Get email preview
     *
     * @param int $storeId
     * @param EmailContentInterface $emailContent
     * @return PreviewInterface
     */
    public function getPreview($storeId, $emailContent);
}
