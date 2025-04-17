<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EmailInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EmailInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const EVENT_ID              = 'event_id';
    const NAME                  = 'name';
    const EMAIL_SEND_DAYS       = 'email_send_days';
    const EMAIL_SEND_HOURS      = 'email_send_hours';
    const EMAIL_SEND_MINUTES    = 'email_send_minutes';
    const WHEN                  = 'when';
    const CONTENT               = 'content';
    const POSITION              = 'position';
    const STATUS                = 'status';
    const AB_TESTING_MODE       = 'ab_testing_mode';
    const PRIMARY_EMAIL_CONTENT = 'primary_email_content';
    const AB_EMAIL_CONTENT      = 'ab_email_content';

    /**#@+
     * Status values
     */
    const STATUS_DISABLED   = 0;
    const STATUS_ENABLED    = 1;
    /**#@-*/

    /**#@+
     * When values
     */
    const WHEN_AFTER  = 0;
    const WHEN_BEFORE = 1;
    /**#@-*/

    /**
     * No template constant
     */
    const NO_TEMPLATE   = -1;

    /**#@+
     * Version content codes
     */
    const CONTENT_VERSION_A = 1;
    const CONTENT_VERSION_B = 2;
    /**#@-*/

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int|null $emailId
     * @return $this
     */
    public function setId($emailId);

    /**
     * Get event id
     *
     * @return int
     */
    public function getEventId();

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get email send days
     *
     * @return int
     */
    public function getEmailSendDays();

    /**
     * Set email send days
     *
     * @param int $days
     * @return $this
     */
    public function setEmailSendDays($days);

    /**
     * Get email send hours
     *
     * @return int
     */
    public function getEmailSendHours();

    /**
     * Set email send hours
     *
     * @param int $hours
     * @return $this
     */
    public function setEmailSendHours($hours);

    /**
     * Get email send minutes
     *
     * @return int
     */
    public function getEmailSendMinutes();

    /**
     * Set email send minutes
     *
     * @param int $minutes
     * @return $this
     */
    public function setEmailSendMinutes($minutes);

    /**
     * Get when
     *
     * @return int
     */
    public function getWhen();

    /**
     * Set when
     *
     * @param int $when
     * @return $this
     */
    public function setWhen($when);

    /**
     * Get email content
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EmailContentInterface[]
     */
    public function getContent();

    /**
     * Set email content
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EmailContentInterface[] $emailContent
     * @return $this
     */
    public function setContent($emailContent);

    /**
     * Get position in email chain
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set position in email chain
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get A/B testing mode
     *
     * @return int
     */
    public function getAbTestingMode();

    /**
     * Set A/B testing mode
     *
     * @param int $mode
     * @return $this
     */
    public function setAbTestingMode($mode);

    /**
     * Get primary email content
     *
     * @return int
     */
    public function getPrimaryEmailContent();

    /**
     * Set primary email content
     *
     * @param int $primaryEmailContent
     * @return $this
     */
    public function setPrimaryEmailContent($primaryEmailContent);

    /**
     * Get A/B email content
     *
     * @return int
     */
    public function getAbEmailContent();

    /**
     * Set A/B email content
     *
     * @param int $abEmailContent
     * @return $this
     */
    public function setAbEmailContent($abEmailContent);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EmailExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EmailExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EmailExtensionInterface $extensionAttributes
    );
}
