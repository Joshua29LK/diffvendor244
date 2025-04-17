<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Aheadworks\Followupemail2\Api\Data\EventInterface;

/**
 * Class Event
 * @package Aheadworks\Followupemail2\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Event extends AbstractDb
{
    /**#@+
     * Constants defined for tables
     * used by corresponding entity
     */
    const AW_CUSTOMER_SEGMENT_TABLE_NAME                                = 'aw_fue2_event_aw_customer_segment';
    const AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME                  = 'event_id';
    const AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME   = 'aw_customer_segment_id';
    /**#@-*/

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_event', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(EventInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Get event AW customer segment linkage table name
     *
     * @return string
     */
    public function getEventAwCustomerSegmentTableName()
    {
        return $this->getTable(self::AW_CUSTOMER_SEGMENT_TABLE_NAME);
    }

    /**
     * Clean links to the removed AW customer segment
     *
     * @param int $removedAwCustomerSegmentId
     * @return $this
     */
    public function cleanLinksAfterAwCustomerSegmentRemoval($removedAwCustomerSegmentId)
    {
        if (!empty($removedAwCustomerSegmentId)) {
            $this
                ->getConnection()
                ->delete(
                    $this->getEventAwCustomerSegmentTableName(),
                    [
                        self::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME . ' = ?' =>
                            $removedAwCustomerSegmentId
                    ]
                );
        }
        return $this;
    }
}
