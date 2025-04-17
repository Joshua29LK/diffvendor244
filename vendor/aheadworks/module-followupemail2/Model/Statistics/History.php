<?php
namespace Aheadworks\Followupemail2\Model\Statistics;

use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History as StatisticsHistoryResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class History
 * @package Aheadworks\Followupemail2\Model\Statistics
 * @codeCoverageIgnore
 */
class History extends AbstractModel implements StatisticsHistoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(StatisticsHistoryResource::class);
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
    public function setId($historyId)
    {
        return $this->setData(self::ID, $historyId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailContentId()
    {
        return $this->getData(self::EMAIL_CONTENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailContentId($emailContentId)
    {
        return $this->setData(self::EMAIL_CONTENT_ID, $emailContentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSent()
    {
        return $this->getData(self::SENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSent($sent)
    {
        return $this->setData(self::SENT, $sent);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpened()
    {
        return $this->getData(self::OPENED);
    }

    /**
     * {@inheritdoc}
     */
    public function setOpened($opened)
    {
        return $this->setData(self::OPENED, $opened);
    }

    /**
     * {@inheritdoc}
     */
    public function getClicked()
    {
        return $this->getData(self::CLICKED);
    }

    /**
     * {@inheritdoc}
     */
    public function setClicked($clicked)
    {
        return $this->setData(self::CLICKED, $clicked);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
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
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\StatisticsHistoryExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
