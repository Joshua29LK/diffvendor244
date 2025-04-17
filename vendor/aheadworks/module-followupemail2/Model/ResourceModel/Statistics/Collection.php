<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Statistics;

use Aheadworks\Followupemail2\Model\Statistics;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics as StatisticsResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Statistics
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
        $this->_init(Statistics::class, StatisticsResource::class);
    }

    /**
     * Add filter by event ids
     *
     * @param array $eventIds
     * @return $this
     */
    public function addFilterByEventIds($eventIds)
    {

        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->where('event_id IN (?)', $eventIds)
            ->columns($this->getColumns());

        return $this;
    }

    /**
     * Add filter by email id
     *
     * @param int $emailId
     * @return $this
     */
    public function addFilterByEmailId($emailId)
    {
        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->where('event_email_id = ?', $emailId)
            ->columns($this->getColumns());

        return $this;
    }

    /**
     * Add filter by email content id
     *
     * @param int $emailContentId
     * @return $this
     */
    public function addFilterByEmailContentId($emailContentId)
    {
        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->where('email_content_id = ?', $emailContentId)
            ->columns($this->getColumns());

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    private function getColumns()
    {
        return [
            'sent'      => 'COALESCE(SUM(main_table.sent), 0)',
            'opened'    => 'COALESCE(SUM(main_table.opened), 0)',
            'clicked'   => 'COALESCE(SUM(main_table.clicked), 0)',
        ];
    }

    /**
     * Delete by email content ids
     *
     * @param int[] $emailContentIds
     * @return bool
     */
    public function deleteByEmailContentIds($emailContentIds)
    {
        $connection = $this->getConnection();
        $select = clone $this->getSelect();
        $select->where('email_content_id in (?)', $emailContentIds);
        $deleteQuery = $connection->deleteFromSelect($select, 'main_table');
        try {
            $connection
                ->beginTransaction()
                ->query($deleteQuery);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return false;
        }
        return true;
    }

    /**
     * Update by email content ids
     *
     * @param int[] $emailContentIds
     * @return bool
     */
    public function updateByEmailContentIds($emailContentIds)
    {
        $connection = $this->getConnection();
        $select = clone $this->getSelect();
        $select
            ->reset()
            ->from(['afee' => $this->getTable('aw_fue2_event_email')], '')
            ->joinInner(
                ['afeec' => $this->getTable('aw_fue2_event_email_content')],
                'afeec.email_id = afee.id',
                ['']
            )
            ->joinLeft(
                ['afsh' => $this->getTable('aw_fue2_statistics_history')],
                'afsh.email_content_id = afeec.id',
                ['']
            )
            ->where('afeec.id in (?)', $emailContentIds)
            ->columns([
                'event_id'          => 'afee.event_id',
                'event_email_id'    => 'afee.id',
                'email_content_id'  => 'afeec.id',
                'sent'              => 'COALESCE(SUM(afsh.sent), 0)',
                'opened'            => 'COALESCE(SUM(afsh.opened), 0)',
                'clicked'           => 'COALESCE(SUM(afsh.clicked), 0)'
            ]);

        $insertQuery = $connection->insertFromSelect(
            $select,
            $this->getMainTable(),
            ['event_id', 'event_email_id', 'email_content_id', 'sent', 'opened', 'clicked']
        );
        try {
            $connection
                ->beginTransaction()
                ->query($insertQuery);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return false;
        }
        return true;
    }
}
