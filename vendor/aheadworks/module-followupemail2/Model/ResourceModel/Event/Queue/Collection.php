<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Model\Event\Queue;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue as QueueResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = EventQueueInterface::ID;

    /**
     * {@inheritdoc}
     */
    protected $_map = [
        'fields' => [
            'id'            => 'main_table.id',
            'event_type'    => 'main_table.event_type',
            'campaign_name' => 'campaign.name',
            'event_name'    => 'event.name',
            'status'        => 'main_table.status',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Queue::class, QueueResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->processItems();
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
     * Process collection items after load
     *
     * @return void
     */
    private function processItems()
    {
        $queueIds = $this->getColumnValues('id');
        if (count($queueIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['email_linkage_table' => $this->getTable('aw_fue2_event_queue_email')])
                ->where('email_linkage_table.event_queue_id IN (?)', $queueIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $emails = [];
                $queueId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['event_queue_id'] == $queueId) {
                        $emails[] = $data;
                    }
                }
                $item->setData(EventQueueInterface::EMAILS, $emails);
            }
        }
    }

    /**
     * Filter collection by event queue email id
     *
     * @param int $eventQueueEmailId
     * @return $this
     */
    public function filterByEventQueueEmailId($eventQueueEmailId)
    {
        $this->getSelect()
            ->joinInner(
                ['event_queue_email' => $this->getTable('aw_fue2_event_queue_email')],
                'main_table.id = event_queue_email.event_queue_id',
                []
            )
            ->where('event_queue_email.id = ?', $eventQueueEmailId);
        ;
        return $this;
    }

    /**
     * Join with event table
     *
     * @return $this
     */
    public function joinCampaign()
    {
        $this->getSelect()
            ->join(
                ['event' => $this->getTable('aw_fue2_event')],
                'main_table.event_id = event.id',
                ['event_name' => 'event.name']
            )
            ->join(
                ['campaign' => $this->getTable('aw_fue2_campaign')],
                'event.campaign_id = campaign.id',
                [
                    'campaign_id'   => 'campaign.id',
                    'campaign_name' => 'campaign.name'
                ]
            )
        ;
        return $this;
    }
}
