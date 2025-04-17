<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\StatisticsHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\Collection as StatisticsCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\CollectionFactory as StatisticsCollectionFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\Updater as StatisticsUpdater;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\Updater as StatisticsHistoryUpdater;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class StatisticsManagement
 * @package Aheadworks\Followupemail2\Model
 */
class StatisticsManagement implements StatisticsManagementInterface
{
    /**
     * Number of decimals
     */
    const DECIMALS = 2;

    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @var StatisticsInterfaceFactory
     */
    private $statisticsInterfaceFactory;

    /**
     * @var StatisticsCollectionFactory
     */
    private $statisticsCollectionFactory;

    /**
     * @var StatisticsHistoryInterfaceFactory
     */
    private $statisticsHistoryInterfaceFactory;

    /**
     * @var StatisticsHistoryRepositoryInterface
     */
    private $statisticsHistoryRepository;

    /**
     * @var StatisticsUpdater
     */
    private $statisticsUpdater;

    /**
     * @var StatisticsHistoryUpdater
     */
    private $statisticsHistoryUpdater;

    /**
     * @param EventManagementInterface $eventManagement
     * @param StatisticsInterfaceFactory $statisticsInterfaceFactory
     * @param StatisticsCollectionFactory $statisticsCollectionFactory
     * @param StatisticsHistoryInterfaceFactory $statisticsHistoryInterfaceFactory
     * @param StatisticsHistoryRepositoryInterface $statisticsHistoryRepository
     * @param StatisticsUpdater $statisticsUpdater
     * @param StatisticsHistoryUpdater $statisticsHistoryUpdater
     */
    public function __construct(
        EventManagementInterface $eventManagement,
        StatisticsInterfaceFactory $statisticsInterfaceFactory,
        StatisticsCollectionFactory $statisticsCollectionFactory,
        StatisticsHistoryInterfaceFactory $statisticsHistoryInterfaceFactory,
        StatisticsHistoryRepositoryInterface $statisticsHistoryRepository,
        StatisticsUpdater $statisticsUpdater,
        StatisticsHistoryUpdater $statisticsHistoryUpdater
    ) {
        $this->eventManagement = $eventManagement;
        $this->statisticsInterfaceFactory = $statisticsInterfaceFactory;
        $this->statisticsCollectionFactory = $statisticsCollectionFactory;
        $this->statisticsHistoryInterfaceFactory = $statisticsHistoryInterfaceFactory;
        $this->statisticsHistoryRepository = $statisticsHistoryRepository;
        $this->statisticsUpdater = $statisticsUpdater;
        $this->statisticsHistoryUpdater = $statisticsHistoryUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function addNew($email, $emailContentId)
    {
        /** @var StatisticsHistoryInterface $statHistory */
        $statHistory = $this->statisticsHistoryInterfaceFactory->create();
        $statHistory
            ->setEmail($email)
            ->setEmailContentId($emailContentId)
            ->setSent(0)
            ->setOpened(0)
            ->setClicked(0);
        try {
            $statHistory = $this->statisticsHistoryRepository->save($statHistory);
        } catch (\Exception $e) {
            return null;
        }

        return $statHistory;
    }

    /**
     * {@inheritdoc}
     */
    public function addSent($statId, $email)
    {
        try {
            /** @var StatisticsHistoryInterface $statHistory */
            $statHistory = $this->statisticsHistoryRepository->get($statId);
            $sent = $statHistory->getSent();
            if (!$sent && $statHistory->getEmail() == $email) {
                $statHistory->setSent(1);
                $this->statisticsHistoryRepository->save($statHistory);
                $this->updateByEmailContentId($statHistory->getEmailContentId());
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addOpened($statId, $email)
    {
        try {
            /** @var StatisticsHistoryInterface $statHistory */
            $statHistory = $this->statisticsHistoryRepository->get($statId);
            $opened = $statHistory->getOpened();
            if (!$opened && $statHistory->getEmail() == $email) {
                $statHistory->setOpened(1);
                $this->statisticsHistoryRepository->save($statHistory);
                $this->updateByEmailContentId($statHistory->getEmailContentId());
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addClicked($statId, $email)
    {
        try {
            /** @var StatisticsHistoryInterface $statHistory */
            $statHistory = $this->statisticsHistoryRepository->get($statId);
            $clicked = $statHistory->getClicked();
            if (!$clicked && $statHistory->getEmail() == $email) {
                $statHistory->setClicked(1);
                $this->statisticsHistoryRepository->save($statHistory);
                $this->updateByEmailContentId($statHistory->getEmailContentId());
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCampaignId($campaignId)
    {
        /** @var EventInterface[] $events */
        $events = $this->eventManagement->getEventsByCampaignId($campaignId);
        $eventIds = [];
        /** @var EventInterface $event */
        foreach ($events as $event) {
            $eventIds[] =  $event->getId();
        }

        /** @var StatisticsCollection $collection */
        $collection = $this->statisticsCollectionFactory->create();
        $collection->addFilterByEventIds($eventIds);

        $item = $collection->getFirstItem();
        if ($item) {
            return $this->getStatistics($item->getSent(), $item->getOpened(), $item->getClicked());
        }
        return $this->getStatistics(0, 0, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventId($eventId)
    {
        /** @var StatisticsCollection $collection */
        $collection = $this->statisticsCollectionFactory->create();
        $collection->addFilterByEventIds([$eventId]);
        $item = $collection->getFirstItem();
        if ($item) {
            return $this->getStatistics($item->getSent(), $item->getOpened(), $item->getClicked());
        }
        return $this->getStatistics(0, 0, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEmailId($emailId)
    {
        /** @var StatisticsCollection $collection */
        $collection = $this->statisticsCollectionFactory->create();
        $collection->addFilterByEmailId($emailId);
        $item = $collection->getFirstItem();
        if ($item) {
            return $this->getStatistics($item->getSent(), $item->getOpened(), $item->getClicked());
        }
        return $this->getStatistics(0, 0, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEmailContentId($emailContentId)
    {
        /** @var StatisticsCollection $collection */
        $collection = $this->statisticsCollectionFactory->create();
        $collection->addFilterByEmailContentId($emailContentId);
        $item = $collection->getFirstItem();
        if ($item) {
            return $this->getStatistics($item->getSent(), $item->getOpened(), $item->getClicked());
        }
        return $this->getStatistics(0, 0, 0);
    }

    /**
     * Get statistics
     *
     * @param int $sent
     * @param int $opened
     * @param int $clicked
     * @return StatisticsInterface
     */
    private function getStatistics($sent, $opened, $clicked)
    {
        /** @var StatisticsInterface $statistics */
        $statistics = $this->statisticsInterfaceFactory->create();

        $openRate = 0;
        $clickRate = 0;
        if ($sent > 0) {
            $openRate = $opened / $sent * 100;
            $clickRate = $clicked / $sent * 100;
        }

        $statistics
            ->setSent($sent)
            ->setOpened($opened)
            ->setClicked($clicked)
            ->setOpenRate(number_format($openRate, self::DECIMALS))
            ->setClickRate(number_format($clickRate, self::DECIMALS));

        return $statistics;
    }

    /**
     * {@inheritdoc}
     */
    public function updateByCampaignId($campaignId)
    {
        return $this->statisticsUpdater->updateByCampaignIds($campaignId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateByEventId($eventId)
    {
        return $this->statisticsUpdater->updateByEventIds($eventId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateByEmailId($emailId)
    {
        return $this->statisticsUpdater->updateByEmailIds($emailId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateByEmailContentId($emailContentId)
    {
        return $this->statisticsUpdater->updateByEmailContentIds($emailContentId);
    }

    /**
     * {@inheritdoc}
     */
    public function resetByCampaignId($campaignId)
    {
        if ($this->statisticsHistoryUpdater->deleteByCampaignIds($campaignId)) {
            return $this->statisticsUpdater->updateByCampaignIds($campaignId);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetByEventId($eventId)
    {
        if ($this->statisticsHistoryUpdater->deleteByEventIds($eventId)) {
            return $this->statisticsUpdater->updateByEventIds($eventId);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetByEmailId($emailId)
    {
        if ($this->statisticsHistoryUpdater->deleteByEmailIds($emailId)) {
            return $this->statisticsUpdater->updateByEmailIds($emailId);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetByEmailContentId($emailContentId)
    {
        if ($this->statisticsHistoryUpdater->deleteByEmailContentIds($emailContentId)) {
            return $this->statisticsUpdater->updateByEmailContentIds($emailContentId);
        }
        return false;
    }
}
