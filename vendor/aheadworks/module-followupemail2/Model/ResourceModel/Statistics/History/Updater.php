<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\Collection as StatHistoryCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\CollectionFactory as StatHistoryCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Updater
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Statistics
 */
class Updater
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var StatHistoryCollectionFactory
     */
    private $statHistoryCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param EmailRepositoryInterface $emailRepository
     * @param StatHistoryCollectionFactory $statHistoryCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        EmailRepositoryInterface $emailRepository,
        StatHistoryCollectionFactory $statHistoryCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->eventRepository = $eventRepository;
        $this->emailRepository = $emailRepository;
        $this->statHistoryCollectionFactory = $statHistoryCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Delete by email content id
     *
     * @param int|int[] $emailContentIds
     * @return bool
     */
    public function deleteByEmailContentIds($emailContentIds)
    {
        if (!is_array($emailContentIds)) {
            $emailContentIds = [$emailContentIds];
        }
        /** @var StatHistoryCollection $statHistoryCollection */
        $statHistoryCollection = $this->statHistoryCollectionFactory->create();
        if (!$statHistoryCollection->deleteByEmailContentIds($emailContentIds)) {
            return false;
        }

        return true;
    }

    /**
     * Delete by email ids
     *
     * @param int|int[] $emailIds
     * @return bool
     */
    public function deleteByEmailIds($emailIds)
    {
        if (!is_array($emailIds)) {
            $emailIds = [$emailIds];
        }

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::ID, $emailIds, 'in');

        /** @var EmailSearchResultsInterface $result */
        $result = $this->emailRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $contentIds = [];
        /** @var EmailInterface $email */
        foreach ($result->getItems() as $email) {
            /** @var EmailContentInterface $content */
            foreach ($email->getContent() as $content) {
                $contentIds[] = $content->getId();
            }
        }

        if (count($contentIds) > 0) {
            return $this->deleteByEmailContentIds($contentIds);
        }
        return false;
    }

    /**
     * Delete by event ids
     *
     * @param int|int[] $eventIds
     * @return bool
     */
    public function deleteByEventIds($eventIds)
    {
        if (!is_array($eventIds)) {
            $eventIds = [$eventIds];
        }

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $eventIds, 'in');

        /** @var EmailSearchResultsInterface $result */
        $result = $this->emailRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $contentIds = [];
        /** @var EmailInterface $email */
        foreach ($result->getItems() as $email) {
            /** @var EmailContentInterface $content */
            foreach ($email->getContent() as $content) {
                $contentIds[] = $content->getId();
            }
        }

        if (count($contentIds) > 0) {
            return $this->deleteByEmailContentIds($contentIds);
        }
        return false;
    }

    /**
     * Delete by campaign ids
     *
     * @param int|int[] $campaignIds
     * @return bool
     */
    public function deleteByCampaignIds($campaignIds)
    {
        if (!is_array($campaignIds)) {
            $campaignIds = [$campaignIds];
        }

        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignIds, 'in');

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $eventIds = [];
        /** @var EventInterface $event */
        foreach ($result->getItems() as $event) {
            $eventIds[] = $event->getId();
        }

        if (count($eventIds) > 0) {
            return $this->deleteByEventIds($eventIds);
        }
        return false;
    }
}
