<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Event
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Event extends AbstractModel implements EventInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EventResource::class);
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
    public function setId($eventId)
    {
        return $this->setData(self::ID, $eventId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCampaignId()
    {
        return $this->getData(self::CAMPAIGN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCampaignId($campaignId)
    {
        return $this->setData(self::CAMPAIGN_ID, $campaignId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return $this->getData(self::EVENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventType($eventType)
    {
        return $this->setData(self::EVENT_TYPE, $eventType);
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
    public function getBccEmails()
    {
        return $this->getData(self::BCC_EMAILS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBccEmails($bccEmails)
    {
        return $this->setData(self::BCC_EMAILS, $bccEmails);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsletterOnly()
    {
        return $this->getData(self::NEWSLETTER_ONLY);
    }

    /**
     * {@inheritdoc}
     */
    public function setNewsletterOnly($newsletterOnly)
    {
        return $this->setData(self::NEWSLETTER_ONLY, $newsletterOnly);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedEmailsMode()
    {
        return $this->getData(self::FAILED_EMAILS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFailedEmailsMode($failedEmailsMode)
    {
        return $this->setData(self::FAILED_EMAILS_MODE, $failedEmailsMode);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductTypeIds()
    {
        return $this->getData(self::PRODUCT_TYPE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductTypeIds($productTypeIds)
    {
        return $this->setData(self::PRODUCT_TYPE_IDS, $productTypeIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartConditions()
    {
        return $this->getData(self::CART_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartConditions($cartConditions)
    {
        return $this->setData(self::CART_CONDITIONS, $cartConditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductConditions()
    {
        return $this->getData(self::PRODUCT_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductConditions($productConditions)
    {
        return $this->setData(self::PRODUCT_CONDITIONS, $productConditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getLifetimeConditions()
    {
        return $this->getData(self::LIFETIME_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLifetimeConditions($lifetimeConditions)
    {
        return $this->setData(self::LIFETIME_CONDITIONS, $lifetimeConditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroups($customerGroups)
    {
        return $this->setData(self::CUSTOMER_GROUPS, $customerGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatuses()
    {
        return $this->getData(self::ORDER_STATUSES);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderStatuses($orderStatuses)
    {
        return $this->setData(self::ORDER_STATUSES, $orderStatuses);
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
    public function getAwCustomerSegmentIds()
    {
        return $this->getData(self::AW_CUSTOMER_SEGMENT_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAwCustomerSegmentIds($awCustomerSegmentIds)
    {
        return $this->setData(self::AW_CUSTOMER_SEGMENT_IDS, $awCustomerSegmentIds);
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
    public function setExtensionAttributes(EventExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
