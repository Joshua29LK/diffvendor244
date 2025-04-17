<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Content
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email
 * @codeCoverageIgnore
 */
class Content extends AbstractDb
{
    /**#@+
     * Constants defined for table
     * used by corresponding entity
     */
    const MAIN_TABLE_NAME = 'aw_fue2_event_email_content';
    const MAIN_TABLE_ID_FIELD_NAME  = 'id';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE_NAME, self::MAIN_TABLE_ID_FIELD_NAME);
    }
}
