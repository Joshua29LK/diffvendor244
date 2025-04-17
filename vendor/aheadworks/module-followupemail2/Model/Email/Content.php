<?php
namespace Aheadworks\Followupemail2\Model\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Content as EmailContentResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Content
 * @package Aheadworks\Followupemail2\Model\Email
 * @codeCoverageIgnore
 */
class Content extends AbstractModel implements EmailContentInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EmailContentResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($contentId)
    {
        return $this->setData(self::ID, $contentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailId()
    {
        return $this->getData(self::EMAIL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailId($emailId)
    {
        return $this->setData(self::EMAIL_ID, $emailId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderName()
    {
        return $this->getData(self::SENDER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderName($name)
    {
        return $this->setData(self::SENDER_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderEmail()
    {
        return $this->getData(self::SENDER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderEmail($email)
    {
        return $this->setData(self::SENDER_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderTemplate()
    {
        return $this->getData(self::HEADER_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaderTemplate($template)
    {
        return $this->setData(self::HEADER_TEMPLATE, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function getFooterTemplate()
    {
        return $this->getData(self::FOOTER_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFooterTemplate($template)
    {
        return $this->setData(self::FOOTER_TEMPLATE, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(EmailContentExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
