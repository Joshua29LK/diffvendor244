<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email\Content;

use Aheadworks\Followupemail2\Model\Email\Content as EmailContent;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Content as EmailContentResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email\Content
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
        $this->_init(EmailContent::class, EmailContentResource::class);
    }
}
