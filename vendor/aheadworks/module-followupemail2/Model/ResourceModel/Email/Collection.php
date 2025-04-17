<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Model\Email;
use Aheadworks\Followupemail2\Model\ResourceModel\Email as EmailResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email
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
        $this->_init(Email::class, EmailResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachContent();
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
     * Filter collection by event id
     *
     * @param int $eventId
     * @return $this
     */
    public function addFilterByEventId($eventId)
    {
        $this->addFieldToFilter('event_id', ['eq' => $eventId]);
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
     * Attach content to collection items
     *
     * @return void
     */
    private function attachContent()
    {
        $emailIds = $this->getColumnValues('id');
        if (count($emailIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['content_linkage_table' => $this->getTable('aw_fue2_event_email_content')])
                ->where('content_linkage_table.email_id IN (?)', $emailIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $content = null;
                $emailId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data[EmailContentInterface::EMAIL_ID] == $emailId) {
                        $content[] = $data;
                    }
                }
                $item->setData('content', $content);
            }
        }
    }
}
