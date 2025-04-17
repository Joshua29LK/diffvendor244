<?php
declare(strict_types = 1);

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Model\Account\ReferringCode\Validator as RefCodeValidator;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class Account extends \Magento\Framework\Model\AbstractModel implements AccountInterface
{
    /**
     * @var RefferingCodesManagement
     */
    private $refCodeManagement;

    /**
     * @var RefCodeValidator
     */
    private $refCodeValidator;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        RefferingCodesManagement $refCodeManagement,
        RefCodeValidator $refCodeValidator,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->refCodeManagement = $refCodeManagement;
        $this->refCodeValidator = $refCodeValidator;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Affiliate\Model\ResourceModel\Account::class);
        $this->setIdFieldName('account_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountId()
    {
        return $this->_getData(AccountInterface::ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAccountId($accountId)
    {
        $this->setData(AccountInterface::ACCOUNT_ID, $accountId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData(AccountInterface::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        $this->setData(AccountInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAffiliateActive()
    {
        return $this->_getData(AccountInterface::IS_AFFILIATE_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAffiliateActive($isAffiliateActive)
    {
        $this->setData(AccountInterface::IS_AFFILIATE_ACTIVE, $isAffiliateActive);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptedTermsConditions()
    {
        return $this->_getData(AccountInterface::ACCEPTED_TERMS_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAcceptedTermsConditions($acceptedTermsConditions)
    {
        $this->setData(AccountInterface::ACCEPTED_TERMS_CONDITIONS, $acceptedTermsConditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReceiveNotifications()
    {
        return $this->_getData(AccountInterface::RECEIVE_NOTIFICATIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setReceiveNotifications($receiveNotifications)
    {
        $this->setData(AccountInterface::RECEIVE_NOTIFICATIONS, $receiveNotifications);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaypalEmail()
    {
        return $this->_getData(AccountInterface::PAYPAL_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaypalEmail($paypalEmail)
    {
        $this->setData(AccountInterface::PAYPAL_EMAIL, $paypalEmail);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringCode()
    {
        return $this->_getData(AccountInterface::REFERRING_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferringCode($referringCode)
    {
        $this->setData(AccountInterface::REFERRING_CODE, $referringCode);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCustomReferringCode(): bool
    {
        return (bool) $this->_getData(AccountInterface::IS_CUSTOM_REFERRING_CODE);
    }

    /**
     * @param bool $isCustomReferringCode
     *
     * @return $this
     */
    public function setIsCustomReferringCode(bool $isCustomReferringCode)
    {
        $this->setData(AccountInterface::IS_CUSTOM_REFERRING_CODE, $isCustomReferringCode);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringWebsite()
    {
        return $this->_getData(AccountInterface::REFERRING_WEBSITE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferringWebsite($referringWebsite)
    {
        $this->setData(AccountInterface::REFERRING_WEBSITE, $referringWebsite);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        return $this->_getData(AccountInterface::BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($balance)
    {
        $this->setData(AccountInterface::BALANCE, $balance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnHold()
    {
        return $this->_getData(AccountInterface::ON_HOLD);
    }

    /**
     * {@inheritdoc}
     */
    public function setOnHold($onHold)
    {
        $this->setData(AccountInterface::ON_HOLD, $onHold);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionPaid()
    {
        return $this->_getData(AccountInterface::COMMISSION_PAID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionPaid($commissionPaid)
    {
        $this->setData(AccountInterface::COMMISSION_PAID, $commissionPaid);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLifetimeCommission()
    {
        return $this->_getData(AccountInterface::LIFETIME_COMMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLifetimeCommission($lifetimeCommission)
    {
        $this->setData(AccountInterface::LIFETIME_COMMISSION, $lifetimeCommission);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetWidth()
    {
        return $this->_getData(AccountInterface::WIDGET_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetWidth($widgetWidth)
    {
        $this->setData(AccountInterface::WIDGET_WIDTH, $widgetWidth);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetHeight()
    {
        return $this->_getData(AccountInterface::WIDGET_HEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetHeight($widgetHeight)
    {
        $this->setData(AccountInterface::WIDGET_HEIGHT, $widgetHeight);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetTitle()
    {
        return $this->_getData(AccountInterface::WIDGET_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetTitle($widgetTitle)
    {
        $this->setData(AccountInterface::WIDGET_TITLE, $widgetTitle);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetProductsNum()
    {
        return $this->_getData(AccountInterface::WIDGET_PRODUCTS_NUM);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetProductsNum($widgetProductsNum)
    {
        $this->setData(AccountInterface::WIDGET_PRODUCTS_NUM, $widgetProductsNum);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetType()
    {
        return $this->_getData(AccountInterface::WIDGET_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetType($widgetType)
    {
        $this->setData(AccountInterface::WIDGET_TYPE, $widgetType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetShowName()
    {
        return $this->_getData(AccountInterface::WIDGET_SHOW_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetShowName($widgetShowName)
    {
        $this->setData(AccountInterface::WIDGET_SHOW_NAME, $widgetShowName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetShowPrice()
    {
        return $this->_getData(AccountInterface::WIDGET_SHOW_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetShowPrice($widgetShowPrice)
    {
        $this->setData(AccountInterface::WIDGET_SHOW_PRICE, $widgetShowPrice);

        return $this;
    }

    /**
     * Processing account entity before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->shouldRegenerateReferringCode()) {
            $this->setReferringCode($this->refCodeManagement->generateReferringCode());
        }

        return parent::beforeSave();
    }

    /**
     * Checks if referring code should be regenerated
     *
     * @return bool
     */
    private function shouldRegenerateReferringCode()
    {
        $referringCode = $this->getReferringCode();
        $useCustomReferringCode = $this->getIsCustomReferringCode();
        if (!$referringCode && $this->isObjectNew() && !$useCustomReferringCode) {
            return true;
        }

        if ($this->getOrigData(self::IS_CUSTOM_REFERRING_CODE) && !$useCustomReferringCode) {
            return true;
        }

        return false;
    }

    /**
     * Get before save validation rules
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();

        $this->refCodeValidator->setContext($this);
        $validator->addRule($this->refCodeValidator, self::REFERRING_CODE);

        return $validator;
    }
}
