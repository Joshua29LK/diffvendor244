<?php
declare(strict_types = 1);

namespace Amasty\Affiliate\Api\Data;

interface AccountInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ACCOUNT_ID = 'account_id';
    public const CUSTOMER_ID = 'customer_id';
    public const IS_AFFILIATE_ACTIVE = 'is_affiliate_active';
    public const ACCEPTED_TERMS_CONDITIONS = 'accepted_terms_conditions';
    public const RECEIVE_NOTIFICATIONS = 'receive_notifications';
    public const PAYPAL_EMAIL = 'paypal_email';
    public const REFERRING_CODE = 'referring_code';
    public const IS_CUSTOM_REFERRING_CODE = 'is_custom_referring_code';
    public const REFERRING_WEBSITE = 'referring_website';
    public const BALANCE = 'balance';
    public const ON_HOLD = 'on_hold';
    public const COMMISSION_PAID = 'commission_paid';
    public const LIFETIME_COMMISSION = 'lifetime_commission';
    public const WIDGET_WIDTH = 'widget_width';
    public const WIDGET_HEIGHT = 'widget_height';
    public const WIDGET_TITLE = 'widget_title';
    public const WIDGET_PRODUCTS_NUM = 'widget_products_num';
    public const WIDGET_TYPE = 'widget_type';
    public const WIDGET_SHOW_NAME = 'widget_show_name';
    public const WIDGET_SHOW_PRICE = 'widget_show_price';
    public const WIDGET_TYPE_BESTSELLER = 'bestseller';
    public const WIDGET_TYPE_NEW = 'new';
    /**#@-*/

    /**
     * @return int
     */
    public function getAccountId();

    /**
     * @param int $accountId
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setAccountId($accountId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getIsAffiliateActive();

    /**
     * @param int $isAffiliateActive
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setIsAffiliateActive($isAffiliateActive);

    /**
     * @return int
     */
    public function getAcceptedTermsConditions();

    /**
     * @param int $acceptedTermsConditions
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setAcceptedTermsConditions($acceptedTermsConditions);

    /**
     * @return int
     */
    public function getReceiveNotifications();

    /**
     * @param int $receiveNotifications
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setReceiveNotifications($receiveNotifications);

    /**
     * @return string|null
     */
    public function getPaypalEmail();

    /**
     * @param string|null $paypalEmail
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setPaypalEmail($paypalEmail);

    /**
     * @return string|null
     */
    public function getReferringCode();

    /**
     * @param string|null $referringCode
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setReferringCode($referringCode);

    /**
     * @return bool
     */
    public function getIsCustomReferringCode(): bool;

    /**
     * @param bool $isCustomReferringCode
     *
     * @return $this
     */
    public function setIsCustomReferringCode(bool $isCustomReferringCode);

    /**
     * @return string|null
     */
    public function getReferringWebsite();

    /**
     * @param string|null $referringWebsite
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setReferringWebsite($referringWebsite);

    /**
     * @return float
     */
    public function getBalance();

    /**
     * @param float $balance
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setBalance($balance);

    /**
     * @return float
     */
    public function getOnHold();

    /**
     * @param float $onHold
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setOnHold($onHold);

    /**
     * @return float
     */
    public function getCommissionPaid();

    /**
     * @param float $commissionPaid
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setCommissionPaid($commissionPaid);

    /**
     * @return float
     */
    public function getLifetimeCommission();

    /**
     * @param float $lifetimeCommission
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setLifetimeCommission($lifetimeCommission);

    /**
     * @return int
     */
    public function getWidgetWidth();

    /**
     * @param int $widgetWidth
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetWidth($widgetWidth);

    /**
     * @return int
     */
    public function getWidgetHeight();

    /**
     * @param int $widgetHeight
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetHeight($widgetHeight);

    /**
     * @return string|null
     */
    public function getWidgetTitle();

    /**
     * @param string|null $widgetTitle
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetTitle($widgetTitle);

    /**
     * @return int|null
     */
    public function getWidgetProductsNum();

    /**
     * @param int|null $widgetProductsNum
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetProductsNum($widgetProductsNum);

    /**
     * @return string
     */
    public function getWidgetType();

    /**
     * @param string $widgetType
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetType($widgetType);

    /**
     * @return int
     */
    public function getWidgetShowName();

    /**
     * @param int $widgetShowName
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetShowName($widgetShowName);

    /**
     * @return int
     */
    public function getWidgetShowPrice();

    /**
     * @param int $widgetShowPrice
     *
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function setWidgetShowPrice($widgetShowPrice);
}
