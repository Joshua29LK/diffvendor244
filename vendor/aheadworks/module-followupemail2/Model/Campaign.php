<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign as CampaignResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Campaign
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Campaign extends AbstractModel implements CampaignInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(CampaignResource::class);
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
    public function setId($campaignId)
    {
        return $this->setData(self::ID, $campaignId);
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
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
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
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(CampaignExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
