<?php

namespace Amasty\Affiliate\Api\Data;

interface CouponInterface
{
    public const ENTITY_ID = 'entity_id';
    public const ACCOUNT_ID = 'account_id';
    public const PROGRAM_ID = 'program_id';
    public const COUPON_ID = 'coupon_id';
    public const CURRENT_PROFIT = 'current_profit';
    public const IS_SYSTEM = 'is_system';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getAccountId();

    /**
     * @param int $accountId
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     */
    public function setAccountId($accountId);

    /**
     * @return int
     */
    public function getProgramId();

    /**
     * @param int $programId
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     */
    public function setProgramId($programId);

    /**
     * @return int
     */
    public function getCouponId();

    /**
     * @param $couponId
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     */
    public function setCouponId($couponId);

    /**
     * @return int
     */
    public function getCurrentProfit();

    /**
     * @param int $currentProfit
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     */
    public function setCurrentProfit($currentProfit);

    /**
     * @return bool
     */
    public function getIsSystem();

    /**
     * @param bool $isSystem
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     */
    public function setIsSystem($isSystem);
}
