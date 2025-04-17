<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Email as EmailResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Email
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Email extends AbstractModel implements EmailInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EmailResource::class);
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
    public function setId($emailId)
    {
        return $this->setData(self::ID, $emailId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailSendDays()
    {
        return $this->getData(self::EMAIL_SEND_DAYS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSendDays($days)
    {
        return $this->setData(self::EMAIL_SEND_DAYS, $days);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailSendHours()
    {
        return $this->getData(self::EMAIL_SEND_HOURS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSendHours($hours)
    {
        return $this->setData(self::EMAIL_SEND_HOURS, $hours);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailSendMinutes()
    {
        return $this->getData(self::EMAIL_SEND_MINUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSendMinutes($minutes)
    {
        return $this->setData(self::EMAIL_SEND_MINUTES, $minutes);
    }

    /**
     * {@inheritdoc}
     */
    public function getWhen()
    {
        return $this->getData(self::WHEN);
    }

    /**
     * {@inheritdoc}
     */
    public function setWhen($when)
    {
        return $this->setData(self::WHEN, $when);
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
    public function setContent($emailContent)
    {
        return $this->setData(self::CONTENT, $emailContent);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbTestingMode()
    {
        return $this->getData(self::AB_TESTING_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAbTestingMode($mode)
    {
        return $this->setData(self::AB_TESTING_MODE, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryEmailContent()
    {
        return $this->getData(self::PRIMARY_EMAIL_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrimaryEmailContent($primaryEmailContent)
    {
        return $this->setData(self::PRIMARY_EMAIL_CONTENT, $primaryEmailContent);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbEmailContent()
    {
        return $this->getData(self::AB_EMAIL_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAbEmailContent($abEmailContent)
    {
        return $this->setData(self::AB_EMAIL_CONTENT, $abEmailContent);
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
    public function setExtensionAttributes(EmailExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
