<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Queue;

use Aheadworks\Followupemail2\Model\Queue;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue as QueueResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Queue
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
        $this->_init(Queue::class, QueueResource::class);
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
        switch ($field) {
            case 'id':
                $field = 'main_table.id';
                break;
            case 'status':
                $field = 'main_table.status';
                break;
            case 'campaign_name':
                $field = 'campaign.name';
                break;
            case 'event_name':
                $field = 'event.name';
                break;
            case 'event_email_name':
                $field = 'event_email.name';
                break;
            default:
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Filter collection by id
     *
     * @param int $id
     * @return $this
     */
    public function addFilterById($id)
    {
        $this->addFieldToFilter('id', ['eq' => $id]);
        return $this;
    }

    /**
     * Join with event table
     *
     * @return $this
     */
    public function joinEvent()
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

    /**
     * Join with event email table
     *
     * @return $this
     */
    public function joinEventMail()
    {
        $this->getSelect()
            ->join(
                ['event_email' => $this->getTable('aw_fue2_event_email')],
                'main_table.event_email_id = event_email.id',
                ['event_email_name' => 'event_email.name']
            )
        ;
        return $this;
    }
}
