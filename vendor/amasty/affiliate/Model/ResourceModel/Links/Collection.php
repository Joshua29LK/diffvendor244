<?php

namespace Amasty\Affiliate\Model\ResourceModel\Links;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var string $_idFieldName */
    protected $_idFieldName = 'link_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Links', 'Amasty\Affiliate\Model\ResourceModel\Links');
    }

    /**
     * @param $accountId
     * @return array
     */
    public function getTypes($accountId)
    {
        $this->addFieldToFilter('affiliate_account_id', $accountId);
        $this->getSelect()->group('link_type');
        return $this->getColumnValues('link_type');
    }

    /**
     * @param $accountId
     * @param $linkType
     * @return int
     */
    public function getCount($accountId = null, $linkType = null, $elementId = null)
    {
        if ($accountId != null) {
            $this->addFieldToFilter('affiliate_account_id', $accountId);
        }
        if ($linkType != null) {
            $this->addFieldToFilter('link_type', $linkType);
        }
        if ($elementId != null) {
            $this->addFieldToFilter('element_id', $elementId);
        }
        return $this->getSize();
    }
}
