<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface EventInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface EventInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID                        = 'id';
    const CAMPAIGN_ID               = 'campaign_id';
    const EVENT_TYPE                = 'event_type';
    const NAME                      = 'name';
    const BCC_EMAILS                = 'bcc_emails';
    const NEWSLETTER_ONLY           = 'newsletter_only';
    const FAILED_EMAILS_MODE        = 'failed_emails_mode';
    const STORE_IDS                 = 'store_ids';
    const PRODUCT_TYPE_IDS          = 'product_type_ids';
    const CART_CONDITIONS           = 'cart_conditions';
    const PRODUCT_CONDITIONS        = 'product_conditions';
    const LIFETIME_CONDITIONS       = 'lifetime_conditions';
    const CUSTOMER_GROUPS           = 'customer_groups';
    const ORDER_STATUSES            = 'order_statuses';
    const STATUS                    = 'status';
    const AW_CUSTOMER_SEGMENT_IDS   = 'aw_customer_segment_ids';

    /**#@+
     * Event type values
     */
    const TYPE_ABANDONED_CART           = 'abandoned_cart';
    const TYPE_ORDER_STATUS_CHANGED     = 'order_status_changed';
    const TYPE_CUSTOMER_REGISTRATION    = 'customer_registration';
    const TYPE_CUSTOMER_LAST_ACTIVITY   = 'customer_last_activity';
    const TYPE_NEWSLETTER_SUBSCRIPTION  = 'newsletter_subscription';
    const TYPE_CUSTOMER_REVIEW          = 'customer_submit_review';
    const TYPE_WISHLIST_CONTENT_CHANGED = 'wishlist_content_changed';
    const TYPE_CUSTOMER_BIRTHDAY        = 'customer_birthday';
    /**#@-*/

    /**#@+
     * Status values
     */
    const STATUS_DISABLED   = 0;
    const STATUS_ENABLED    = 1;
    /**#@-*/

    /**#@+
     * Conditions types
     */
    const TYPE_CONDITIONS_CART      = 1;
    const TYPE_CONDITIONS_PRODUCT   = 2;
    const TYPE_CONDITIONS_LIFETIME  = 3;
    /**#@-*/

    /**#@+
     * Failed emails mode values
     */
    const FAILED_EMAILS_SKIP    = 1;
    const FAILED_EMAILS_CANCEL  = 2;
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
     * @param int $eventId
     * @return $this
     */
    public function setId($eventId);

    /**
     * Get campaign Id
     *
     * @return int
     */
    public function getCampaignId();

    /**
     * Set campaign Id
     *
     * @param int $campaignId
     * @return $this
     */
    public function setCampaignId($campaignId);

    /**
     * Get event type
     *
     * @return string
     */
    public function getEventType();

    /**
     * Set event type
     *
     * @param string $eventType
     * @return $this
     */
    public function setEventType($eventType);

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
     * Get bcc emails
     *
     * @return string|null
     */
    public function getBccEmails();

    /**
     * Set bcc emails
     *
     * @param string|null $bccEmails
     * @return $this
     */
    public function setBccEmails($bccEmails);

    /**
     * Get send to newsletter only subscribers
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNewsletterOnly();

    /**
     * Set send to newsletter only subscribers
     *
     * @param bool $newsletterOnly
     * @return $this
     */
    public function setNewsletterOnly($newsletterOnly);

    /**
     * Get failed emails mode
     *
     * @return int
     */
    public function getFailedEmailsMode();

    /**
     * Set failed emails mode
     *
     * @param int $failedEmailsMode
     * @return $this
     */
    public function setFailedEmailsMode($failedEmailsMode);

    /**
     * Get store ids
     *
     * @return int[]
     */
    public function getStoreIds();

    /**
     * Set store ids
     *
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds);

    /**
     * Get product type ids
     *
     * @return string[]
     */
    public function getProductTypeIds();

    /**
     * Set product type ids
     *
     * @param string[] $productTypeIds
     * @return $this
     */
    public function setProductTypeIds($productTypeIds);

    /**
     * Get cart conditions (serialized)
     *
     * @return string
     */
    public function getCartConditions();

    /**
     * Set cart conditions (serialized)
     *
     * @param string $cartConditions
     * @return $this
     */
    public function setCartConditions($cartConditions);

    /**
     * Get product conditions (serialized)
     *
     * @return string
     */
    public function getProductConditions();

    /**
     * Set product conditions (serialized)
     *
     * @param string $productConditions
     * @return $this
     */
    public function setProductConditions($productConditions);

    /**
     * Get lifetime conditions (serialized)
     *
     * @return string
     */
    public function getLifetimeConditions();

    /**
     * Set lifetime conditions
     *
     * @param string $lifetimeConditions (serialized)
     * @return $this
     */
    public function setLifetimeConditions($lifetimeConditions);

    /**
     * Get customer groups
     *
     * @return string[]
     */
    public function getCustomerGroups();

    /**
     * Set customer groups
     *
     * @param string[] $customerGroups
     * @return $this
     */
    public function setCustomerGroups($customerGroups);

    /**
     * Get order statuses
     *
     * @return string[]
     */
    public function getOrderStatuses();

    /**
     * Set order statuses
     *
     * @param string[] $orderStatuses
     * @return $this
     */
    public function setOrderStatuses($orderStatuses);

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
     * Get AW customer segment ids
     *
     * @return int[]
     */
    public function getAwCustomerSegmentIds();

    /**
     * Set AW customer segment ids
     *
     * @param int[] $awCustomerSegmentIds
     * @return $this
     */
    public function setAwCustomerSegmentIds($awCustomerSegmentIds);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\EventExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\EventExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\EventExtensionInterface $extensionAttributes
    );
}
