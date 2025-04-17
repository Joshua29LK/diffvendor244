<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote;

use Aheadworks\Followupemail2\Model\Order\Quote as OrderQuote;
use Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote as OrderQuoteResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(OrderQuote::class, OrderQuoteResource::class);
    }

    /**
     * Filter collection by order id
     *
     * @param int $orderId
     * @return $this
     */
    public function addFilterByOrderId($orderId)
    {
        $this->addFieldToFilter('order_id', ['eq' => $orderId]);

        return $this;
    }
}
