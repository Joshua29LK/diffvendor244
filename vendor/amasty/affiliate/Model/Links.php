<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\LinksInterface;
use Amasty\Affiliate\Model\ResourceModel\Links as ResourceLinks;

class Links extends \Magento\Framework\Model\AbstractModel implements LinksInterface
{
    public const TYPE_BANNER = 'banner';
    public const TYPE_LINK = 'link';

    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceLinks::class);
        $this->setIdFieldName('link_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkId()
    {
        return $this->_getData(LinksInterface::LINK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkId($linkId)
    {
        $this->setData(LinksInterface::LINK_ID, $linkId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffiliateAccountId()
    {
        return $this->_getData(LinksInterface::AFFILIATE_ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAffiliateAccountId($affiliateAccountId)
    {
        $this->setData(LinksInterface::AFFILIATE_ACCOUNT_ID, $affiliateAccountId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(LinksInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(LinksInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkType()
    {
        return $this->_getData(LinksInterface::LINK_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkType($linkType)
    {
        $this->setData(LinksInterface::LINK_TYPE, $linkType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementId()
    {
        return $this->_getData(LinksInterface::ELEMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setElementId($elementId)
    {
        $this->setData(LinksInterface::ELEMENT_ID, $elementId);

        return $this;
    }
}
