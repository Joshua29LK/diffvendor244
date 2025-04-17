<?php

namespace Amasty\Affiliate\Api\Data;

interface LifetimeInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const LIFETIME_ID = 'lifetime_id';
    public const AFFILIATE_ACCOUNT_ID = 'affiliate_account_id';
    public const PROGRAM_ID = 'program_id';
    public const CUSTOMER_EMAIL = 'customer_email';
    /**#@-*/

    /**
     * @return int
     */
    public function getLifetimeId();

    /**
     * @param int $lifetimeId
     *
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     */
    public function setLifetimeId($lifetimeId);

    /**
     * @return int
     */
    public function getAffiliateAccountId();

    /**
     * @param int $affiliateAccountId
     *
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     */
    public function setAffiliateAccountId($affiliateAccountId);

    /**
     * @return int
     */
    public function getProgramId();

    /**
     * @param int $programId
     *
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     */
    public function setProgramId($programId);

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     *
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     */
    public function setCustomerEmail($customerEmail);
}
