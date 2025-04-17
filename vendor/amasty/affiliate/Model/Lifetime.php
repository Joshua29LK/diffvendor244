<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\LifetimeInterface;

class Lifetime extends \Magento\Framework\Model\AbstractModel implements LifetimeInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Affiliate\Model\ResourceModel\Lifetime');
        $this->setIdFieldName('lifetime_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getLifetimeId()
    {
        return $this->_getData(LifetimeInterface::LIFETIME_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setLifetimeId($lifetimeId)
    {
        $this->setData(LifetimeInterface::LIFETIME_ID, $lifetimeId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffiliateAccountId()
    {
        return $this->_getData(LifetimeInterface::AFFILIATE_ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAffiliateAccountId($affiliateAccountId)
    {
        $this->setData(LifetimeInterface::AFFILIATE_ACCOUNT_ID, $affiliateAccountId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramId()
    {
        return $this->_getData(LifetimeInterface::PROGRAM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProgramId($programId)
    {
        $this->setData(LifetimeInterface::PROGRAM_ID, $programId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->_getData(LifetimeInterface::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->setData(LifetimeInterface::CUSTOMER_EMAIL, $customerEmail);

        return $this;
    }
}
