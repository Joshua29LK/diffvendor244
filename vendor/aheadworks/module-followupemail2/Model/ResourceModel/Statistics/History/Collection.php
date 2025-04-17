<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History;

use Aheadworks\Followupemail2\Model\Statistics\History as StatisticsHistory;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History as StatisticsHistoryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History
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
        $this->_init(StatisticsHistory::class, StatisticsHistoryResource::class);
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
}
