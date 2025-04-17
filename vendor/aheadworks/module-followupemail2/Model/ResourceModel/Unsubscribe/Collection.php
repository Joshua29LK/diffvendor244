<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe;

use Aheadworks\Followupemail2\Model\Unsubscribe;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe as UnsubscribeResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe
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
        $this->_init(Unsubscribe::class, UnsubscribeResource::class);
    }
}
