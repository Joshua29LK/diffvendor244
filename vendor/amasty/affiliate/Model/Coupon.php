<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\CouponInterface;
use Magento\Framework\Model\AbstractModel;

class Coupon extends AbstractModel implements CouponInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Affiliate\Model\ResourceModel\Coupon::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->_getData(CouponInterface::ENTITY_ID);
    }

    /**
     * @return int|null
     */
    public function getAccountId()
    {
        return $this->_getData(CouponInterface::ACCOUNT_ID);
    }

    /**
     * @param int $accountId
     * @return $this|CouponInterface
     */
    public function setAccountId($accountId)
    {
        $this->setData(CouponInterface::ACCOUNT_ID, $accountId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProgramId()
    {
        return $this->_getData(CouponInterface::PROGRAM_ID);
    }

    /**
     * @param int $programId
     * @return $this|CouponInterface
     */
    public function setProgramId($programId)
    {
        $this->setData(CouponInterface::PROGRAM_ID, $programId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCouponId()
    {
        return $this->_getData(CouponInterface::COUPON_ID);
    }

    /**
     * @param $couponId
     * @return $this|CouponInterface
     */
    public function setCouponId($couponId)
    {
        $this->setData(CouponInterface::COUPON_ID, $couponId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrentProfit()
    {
        return $this->_getData(CouponInterface::CURRENT_PROFIT);
    }

    /**
     * @param int $currentProfit
     * @return $this|CouponInterface
     */
    public function setCurrentProfit($currentProfit)
    {
        $this->setData(CouponInterface::CURRENT_PROFIT, $currentProfit);
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsSystem()
    {
        return $this->_getData(CouponInterface::IS_SYSTEM);
    }

    /**
     * @param bool $isSystem
     * @return $this|CouponInterface
     */
    public function setIsSystem($isSystem)
    {
        $this->setData(CouponInterface::IS_SYSTEM, $isSystem);
        return $this;
    }
}
