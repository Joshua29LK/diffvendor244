<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EmailContentInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EmailContentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const EMAIL_ID              = 'email_id';
    const SENDER_NAME           = 'sender_name';
    const SENDER_EMAIL          = 'sender_email';
    const SUBJECT               = 'subject';
    const CONTENT               = 'content';
    const HEADER_TEMPLATE       = 'header_template';
    const FOOTER_TEMPLATE       = 'footer_template';

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int|null $contentId
     * @return $this
     */
    public function setId($contentId);

    /**
     * Get email id
     *
     * @return int
     */
    public function getEmailId();

    /**
     * Set email id
     *
     * @param int $emailId
     * @return $this
     */
    public function setEmailId($emailId);

    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName();

    /**
     * Set sender name
     *
     * @param string $name
     * @return $this
     */
    public function setSenderName($name);

    /**
     * Get sender email
     *
     * @return string
     */
    public function getSenderEmail();

    /**
     * Set sender email
     *
     * @param string $email
     * @return $this
     */
    public function setSenderEmail($email);

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
     * @return string
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get header template
     *
     * @return string
     */
    public function getHeaderTemplate();

    /**
     * Set header template
     *
     * @param string $template
     * @return $this
     */
    public function setHeaderTemplate($template);

    /**
     * Get footer template
     *
     * @return string
     */
    public function getFooterTemplate();

    /**
     * Set footer template
     *
     * @param string $template
     * @return $this
     */
    public function setFooterTemplate($template);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EmailContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EmailContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EmailContentExtensionInterface $extensionAttributes
    );
}
