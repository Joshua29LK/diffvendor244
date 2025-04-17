<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Indexer;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\Processor as ScheduledEmailsProcessor;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Indexer\Model\ResourceModel\AbstractResource;

/**
 * Class ScheduledEmails
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Indexer
 */
class ScheduledEmails extends AbstractResource
{
    /**
     * @var int
     */
    const INSERT_PER_QUERY = 2;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var ScheduledEmailsProcessor
     */
    private $scheduledEmailsProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StrategyInterface $tableStrategy
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param ScheduledEmailsProcessor $scheduledEmailsProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        EventQueueRepositoryInterface $eventQueueRepository,
        ScheduledEmailsProcessor $scheduledEmailsProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $connectionName);
        $this->eventQueueRepository = $eventQueueRepository;
        $this->scheduledEmailsProcessor = $scheduledEmailsProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Define main product post index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_scheduled_emails', 'event_queue_id');
    }

    /**
     * Reindex all scheduled emails data
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->clearTemporaryIndexTable();
        $this->beginTransaction();
        try {
            $this->processAll();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        $this->syncData();
        return $this;
    }

    /**
     * Reindex scheduled emails data for defined ids
     *
     * @param array|int $ids
     * @return $this
     * @throws \Exception
     */
    public function reindexRows($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $this->beginTransaction();
        try {
            $this->deleteRows($ids);
            $this->processRows($ids);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Process all rows
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processAll()
    {
        $page = 1;
        $allProcessed = false;
        $recordsCount = 0;
        while (!$allProcessed) {
            $this->searchCriteriaBuilder
                ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING)
                ->setPageSize(self::INSERT_PER_QUERY)
                ->setCurrentPage($page);

            /** @var EventQueueSearchResultsInterface $result */
            $result = $this->eventQueueRepository
                ->getList($this->searchCriteriaBuilder->create());
            /** @var EventQueueInterface[] $eventQueueItems */
            $eventQueueItems = $result->getItems();

            if ($recordsCount >= $result->getTotalCount()) {
                $allProcessed = true;
            } else {
                $scheduledEmailsData = [];
                foreach ($eventQueueItems as $eventQueueItem) {
                    $scheduledEmailsData[] = $this->scheduledEmailsProcessor->getData($eventQueueItem);
                    $recordsCount++;
                }

                if (count($scheduledEmailsData) > 0) {
                    $this->saveData($scheduledEmailsData, true);
                }
            }
            $page++;
        }

        return $this;
    }

    /**
     * Delete rows
     *
     * @param array $ids
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteRows($ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [$this->getIdFieldName() . ' IN (?)' => $ids]
        );

        return $this;
    }

    /**
     * Process rows
     *
     * @param array $ids
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processRows($ids)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::ID, $ids, 'in')
            ->addFilter(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING);

        /** @var EventQueueInterface[] $eventQueueItems */
        $eventQueueItems = $this->eventQueueRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $scheduledEmailsData = [];
        foreach ($eventQueueItems as $eventQueueItem) {
            $scheduledEmailsData[] = $this->scheduledEmailsProcessor->getData($eventQueueItem);
        }

        if (count($scheduledEmailsData) > 0) {
            $this->saveData($scheduledEmailsData, false);
        }

        return $this;
    }

    /**
     * Save data to index or main table
     *
     * @param array $data
     * @param bool|true $intoIndexTable
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveData($data, $intoIndexTable = true)
    {
        $counter = 0;
        $toInsert = [];
        foreach ($data as $row) {
            $counter++;
            $toInsert[] = $row;
            if ($counter % self::INSERT_PER_QUERY == 0) {
                $this->insertToTable($toInsert, $intoIndexTable);
                $toInsert = [];
            }
        }
        $this->insertToTable($toInsert, $intoIndexTable);
        return $this;
    }

    /**
     * Insert to index table
     *
     * @param array $toInsert
     * @param bool|true $intoIndexTable
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function insertToTable($toInsert, $intoIndexTable = true)
    {
        $table = $intoIndexTable
            ? $this->getTable($this->getIdxTable())
            : $this->getMainTable();
        if (count($toInsert)) {
            $this->getConnection()->insertMultiple(
                $table,
                $toInsert
            );
        }
        return $this;
    }
}
