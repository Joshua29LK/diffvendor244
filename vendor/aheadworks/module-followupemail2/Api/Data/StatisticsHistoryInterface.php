<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StatisticsHistoryInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface StatisticsHistoryInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const EMAIL             = 'email';
    const EMAIL_CONTENT_ID  = 'email_content_id';
    const SENT              = 'sent';
    const OPENED            = 'opened';
    const CLICKED           = 'clicked';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';
    /**#@-*/

    /**
     * Get history id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set history id
     *
     * @param int|null $historyId
     * @return $this
     */
    public function setId($historyId);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get email content id
     *
     * @return int
     */
    public function getEmailContentId();

    /**
     * Set email content id
     *
     * @param int $emailContentId
     * @return $this
     */
    public function setEmailContentId($emailContentId);

    /**
     * Get sent
     *
     * @return int
     */
    public function getSent();

    /**
     * Set sent
     *
     * @param int $sent
     * @return $this
     */
    public function setSent($sent);

    /**
     * Get opened
     *
     * @return int
     */
    public function getOpened();

    /**
     * Set opened
     *
     * @param int $opened
     * @return $this
     */
    public function setOpened($opened);

    /**
     * Get clicked
     *
     * @return int
     */
    public function getClicked();

    /**
     * Set clicked
     *
     * @param int $clicked
     * @return $this
     */
    public function setClicked($clicked);

    /**
     * Get created time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\StatisticsHistoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\StatisticsHistoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\StatisticsHistoryExtensionInterface $extensionAttributes
    );
}
