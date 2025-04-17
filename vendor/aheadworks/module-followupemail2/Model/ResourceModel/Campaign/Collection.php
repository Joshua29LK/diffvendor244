<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Campaign;

use Aheadworks\Followupemail2\Model\Campaign;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign as CampaignResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Campaign
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
        $this->_init(Campaign::class, CampaignResource::class);
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
     * Add start date filter
     *
     * @param string $startDate
     * @return $this
     */
    public function addStartDateFilter($startDate)
    {
        $this
            ->getSelect()
            ->where('(`main_table`.`start_date` IS NULL OR `main_table`.`start_date` <= ?)', $startDate);
        return $this;
    }

    /**
     * Add end date filter
     *
     * @param string $endDate
     * @return $this
     */
    public function addEndDateFilter($endDate)
    {
        $this
            ->getSelect()
            ->where('(`main_table`.`end_date` IS NULL OR `main_table`.`end_date` >= ?)', $endDate);
        return $this;
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
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'entity_id' || $field === 'id') {
            $field = 'main_table.id';
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Join events statistics
     *
     * @return $this
     */
    public function joinEventsStatistics()
    {
        $this->getSelect()
            ->joinLeft(
                ['event' => $this->getTable('aw_fue2_event')],
                'event.campaign_id = main_table.id',
                ['events_count' => new \Zend_Db_Expr('COALESCE(COUNT(event.id), 0)')]
            )
            ->joinLeft(
                [
                    'email' => new \Zend_Db_Expr(
                        '(SELECT event_id, COUNT(id) AS emails_count
                        FROM ' . $this->getTable('aw_fue2_event_email') . ' GROUP BY event_id)'
                    )
                ],
                'email.event_id = event.id',
                ['emails_count' => new \Zend_Db_Expr('COALESCE(SUM(email.emails_count), 0)')]
            )
            ->joinLeft(
                ['statistics' => new \Zend_Db_Expr(
                    '(SELECT event_id, COALESCE(SUM(sent), 0) AS sent,
                    COALESCE(SUM(opened), 0) AS opened, COALESCE(SUM(clicked), 0) AS clicked
                    FROM ' . $this->getTable('aw_fue2_statistics') . ' GROUP BY event_id)'
                )],
                'statistics.event_id = event.id',
                [
                    'sent' => new \Zend_Db_Expr('COALESCE(SUM(statistics.sent), 0)'),
                    'opened' => new \Zend_Db_Expr('COALESCE(SUM(statistics.opened), 0)'),
                    'clicked' => new \Zend_Db_Expr('COALESCE(SUM(statistics.clicked), 0)'),
                    'open_rate' => new \Zend_Db_Expr(
                        'COALESCE(SUM(statistics.opened) / SUM(statistics.sent), 0) * 100'
                    ),
                    'click_rate' => new \Zend_Db_Expr(
                        'COALESCE(SUM(statistics.clicked) / SUM(statistics.sent), 0) * 100'
                    ),
                ]
            )
            ->group('main_table.id')
            ->group('main_table.name')
        ;
        return $this;
    }
}
