<?php
namespace Aheadworks\Followupemail2\Api\Data;

/**
 * Interface PreviewInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface PreviewInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const STORE_ID        = 'store_id';
    const SENDER_NAME     = 'sender_name';
    const SENDER_EMAIL    = 'sender_email';
    const RECIPIENT_NAME  = 'recipient_name';
    const RECIPIENT_EMAIL = 'recipient_email';
    const SUBJECT         = 'subject';
    const CONTENT         = 'content';
    /**#@-*/

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName();

    /**
     * Set sender name
     *
     * @param string $senderName
     * @return $this
     */
    public function setSenderName($senderName);

    /**
     * Get recipient email
     *
     * @return string
     */
    public function getSenderEmail();

    /**
     * Set recipient email
     *
     * @param string $senderName
     * @return $this
     */
    public function setSenderEmail($senderName);

    /**
     * Get recipient name
     *
     * @return string
     */
    public function getRecipientName();

    /**
     * Set recipient name
     *
     * @param string $recipientName
     * @return $this
     */
    public function setRecipientName($recipientName);

    /**
     * Get recipient email
     *
     * @return string
     */
    public function getRecipientEmail();

    /**
     * Set recipient email
     *
     * @param string $recipientEmail
     * @return $this
     */
    public function setRecipientEmail($recipientEmail);

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);
}
