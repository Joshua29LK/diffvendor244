<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Config;

use Aheadworks\Followupemail2\Model\Config as ConfigModel;
use Aheadworks\Followupemail2\Model\ResourceModel\Config as ConfigResource;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Config
 * @codeCoverageIgnore
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ConfigModel::class, ConfigResource::class);
    }
}
