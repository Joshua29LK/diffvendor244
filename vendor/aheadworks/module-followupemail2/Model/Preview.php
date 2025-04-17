<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Preview
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Preview extends AbstractSimpleObject implements PreviewInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderName()
    {
        return $this->_get(self::SENDER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderName($senderName)
    {
        return $this->setData(self::SENDER_NAME, $senderName);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderEmail()
    {
        return $this->_get(self::SENDER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderEmail($senderEmail)
    {
        return $this->setData(self::SENDER_EMAIL, $senderEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientName()
    {
        return $this->_get(self::RECIPIENT_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientName($recipientName)
    {
        return $this->setData(self::RECIPIENT_NAME, $recipientName);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientEmail()
    {
        return $this->_get(self::RECIPIENT_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientEmail($recipientEmail)
    {
        return $this->setData(self::RECIPIENT_EMAIL, $recipientEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->_get(self::SUBJECT);
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
        return $this->_get(self::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }
}
