<?php

namespace Amasty\Affiliate\Api\Data;

interface LinksInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const LINK_ID = 'link_id';
    public const AFFILIATE_ACCOUNT_ID = 'affiliate_account_id';
    public const CREATED_AT = 'created_at';
    public const LINK_TYPE = 'link_type';
    public const ELEMENT_ID = 'element_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getLinkId();

    /**
     * @param int $linkId
     *
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     */
    public function setLinkId($linkId);

    /**
     * @return int
     */
    public function getAffiliateAccountId();

    /**
     * @param int $affiliateAccountId
     *
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     */
    public function setAffiliateAccountId($affiliateAccountId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getLinkType();

    /**
     * @param string|null $linkType
     *
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     */
    public function setLinkType($linkType);

    /**
     * @return int|null
     */
    public function getElementId();

    /**
     * @param int|null $elementId
     *
     * @return \Amasty\Affiliate\Api\Data\LinksInterface
     */
    public function setElementId($elementId);
}
