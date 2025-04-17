<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event;
use Aheadworks\Followupemail2\Model\ResourceModel\Event as EventResource;
use Aheadworks\Followupemail2\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event
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
        $this->_init(Event::class, EventResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->processItems();
        $this->attachCartConditionsColumn();
        $this->attachProductConditionsColumn();
        $this->attachLifetimeConditionsColumn();
        $this->attachAwCustomerSegmentIdsColumn();
        return parent::_afterLoad();
    }

    /**
     * Filter collection by status
     *
     * @param int $status
     * @return $this
     */
    public function addFilterByStatus($status)
    {
        if (is_int($status)) {
            $this->addFieldToFilter('status', ['eq' => $status]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'entity_id') {
            $field = 'id';
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Process collection items after load
     *
     * @return void
     */
    private function processItems()
    {
        $eventIds = $this->getColumnValues('id');
        if (count($eventIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['store_linkage_table' => $this->getTable('aw_fue2_event_store')])
                ->where('store_linkage_table.event_id IN (?)', $eventIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $stores = [];
                $eventId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['event_id'] == $eventId) {
                        $stores[] = $data['store_id'];
                    }
                }
                $item->setData(EventInterface::STORE_IDS, $stores);
                if (!is_array($item->getData(EventInterface::CUSTOMER_GROUPS))) {
                    $item->setData(
                        EventInterface::CUSTOMER_GROUPS,
                        explode(',', $item->getData(EventInterface::CUSTOMER_GROUPS) ?? '')
                    );
                }
                if (!is_array($item->getData(EventInterface::PRODUCT_TYPE_IDS))) {
                    $item->setData(
                        EventInterface::PRODUCT_TYPE_IDS,
                        explode(',', $item->getData(EventInterface::PRODUCT_TYPE_IDS) ?? '')
                    );
                }
                if (!is_array($item->getData(EventInterface::ORDER_STATUSES))) {
                    $item->setData(
                        EventInterface::ORDER_STATUSES,
                        explode(',', $item->getData(EventInterface::ORDER_STATUSES) ?? '')
                    );
                }
            }
        }
    }

    /**
     * Attach cart conditions column
     *
     * @return $this
     */
    private function attachCartConditionsColumn()
    {
        return $this->attachRelationTable(
            'aw_fue2_event_conditions',
            EventInterface::ID,
            'event_id',
            'value',
            EventInterface::CART_CONDITIONS,
            [
                ['field' => 'type', 'condition' => '=', 'value' => EventInterface::TYPE_CONDITIONS_CART]
            ]
        );
    }

    /**
     * Attach product conditions column
     *
     * @return $this
     */
    private function attachProductConditionsColumn()
    {
        return $this->attachRelationTable(
            'aw_fue2_event_conditions',
            EventInterface::ID,
            'event_id',
            'value',
            EventInterface::PRODUCT_CONDITIONS,
            [
                ['field' => 'type', 'condition' => '=', 'value' => EventInterface::TYPE_CONDITIONS_PRODUCT]
            ]
        );
    }

    /**
     * Attach product conditions column
     *
     * @return $this
     */
    private function attachLifetimeConditionsColumn()
    {
        return $this->attachRelationTable(
            'aw_fue2_event_conditions',
            EventInterface::ID,
            'event_id',
            'value',
            EventInterface::LIFETIME_CONDITIONS,
            [
                ['field' => 'type', 'condition' => '=', 'value' => EventInterface::TYPE_CONDITIONS_LIFETIME]
            ]
        );
    }

    /**
     * Attach AW customer segment column
     *
     * @return $this
     */
    private function attachAwCustomerSegmentIdsColumn()
    {
        return $this->attachRelationTable(
            EventResource::AW_CUSTOMER_SEGMENT_TABLE_NAME,
            EventInterface::ID,
            EventResource::AW_CUSTOMER_SEGMENT_TABLE_LINKAGE_FIELD_NAME,
            EventResource::AW_CUSTOMER_SEGMENT_TABLE_AW_CUSTOMER_SEGMENT_ID_FIELD_NAME,
            EventInterface::AW_CUSTOMER_SEGMENT_IDS,
            [],
            [],
            true
        );
    }
}
