<?php
namespace Aheadworks\Followupemail2\Model\Indexer;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Full as ScheduledEmailsActionFull;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Rows as ScheduledEmailsActionRows;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Row as ScheduledEmailsActionRow;

/**
 * Class ScheduledEmails
 *
 * @package Aheadworks\AdvancedReports\Model\Indexer
 */
class ScheduledEmails implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var ScheduledEmailsActionFull
     */
    private $scheduledEmailsActionFull;

    /**
     * @var ScheduledEmailsActionRows
     */
    private $scheduledEmailsActionRows;

    /**
     * @var ScheduledEmailsActionRow
     */
    private $scheduledEmailsActionRow;

    /**
     * @param ScheduledEmailsActionFull $scheduledEmailsActionFull
     * @param ScheduledEmailsActionRows $scheduledEmailsActionRows
     * @param ScheduledEmailsActionRow $scheduledEmailsActionRow
     */
    public function __construct(
        ScheduledEmailsActionFull $scheduledEmailsActionFull,
        ScheduledEmailsActionRows $scheduledEmailsActionRows,
        ScheduledEmailsActionRow $scheduledEmailsActionRow
    ) {
        $this->scheduledEmailsActionFull = $scheduledEmailsActionFull;
        $this->scheduledEmailsActionRows = $scheduledEmailsActionRows;
        $this->scheduledEmailsActionRow = $scheduledEmailsActionRow;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        $this->scheduledEmailsActionRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeFull()
    {
        $this->scheduledEmailsActionFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->scheduledEmailsActionRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRow($id)
    {
        $this->scheduledEmailsActionRow->execute($id);
    }
}
