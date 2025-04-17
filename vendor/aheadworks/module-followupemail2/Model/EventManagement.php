<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\Unsubscribe\Service as UnsubscribeService;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class EventManagement
 * @package Aheadworks\Followupemail2\Model
 */
class EventManagement implements EventManagementInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var UnsubscribeService
     */
    private $unsubscribeService;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Config $config
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventRepositoryInterface $eventRepository
     * @param EmailRepositoryInterface $emailRepository
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param UnsubscribeService $unsubscribeService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        Config $config,
        CampaignRepositoryInterface $campaignRepository,
        EventRepositoryInterface $eventRepository,
        EmailRepositoryInterface $emailRepository,
        EventQueueRepositoryInterface $eventQueueRepository,
        UnsubscribeService $unsubscribeService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer
    ) {
        $this->config = $config;
        $this->campaignRepository = $campaignRepository;
        $this->eventRepository = $eventRepository;
        $this->emailRepository = $emailRepository;
        $this->eventQueueRepository = $eventQueueRepository;
        $this->unsubscribeService = $unsubscribeService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicateEventEmails($sourceEventId, $destinationEventId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $sourceEventId, 'eq');

        /** @var EmailSearchResultsInterface $result */
        $result = $this->emailRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        /** @var EmailInterface $email */
        foreach ($result->getItems() as $email) {
            /** @var EmailInterface $emailDataObject */
            $emailDataObject = $this->emailRepository->get($email->getId());
            $emailDataObject->setId(null);
            /** @var EmailContentInterface[] $emailContent */
            $emailContent = $emailDataObject->getContent();
            /** @var EmailContentInterface $content */
            foreach ($emailContent as &$content) {
                $content->setId(null);
                $content->setEmailId(null);
            }

            $emailDataObject->setContent($emailContent);

            $emailDataObject->setEventId($destinationEventId);
            $this->emailRepository->save($emailDataObject);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsByCampaignId($campaignId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventInterface::CAMPAIGN_ID, $campaignId, 'eq');

        /** @var EventSearchResultsInterface $result */
        $result = $this->eventRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        return $result->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribeFromEvent($securityCode)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::SECURITY_CODE, $securityCode, 'eq');

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($result->getItems() as $eventQueueItem) {
            $eventId = $eventQueueItem->getEventId();
            $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
            $email = isset($eventData['email']) ? $eventData['email'] : '';
            $storeId = isset($eventData['store_id']) ? $eventData['store_id'] : 0;
            if (!$this->config->isTestModeEnabled($storeId) && $email) {
                $this->unsubscribeService->unsubscribeFromEvent($eventId, $email, $storeId);
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribeFromEventType($securityCode)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::SECURITY_CODE, $securityCode, 'eq');

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($result->getItems() as $eventQueueItem) {
            $eventType = $eventQueueItem->getEventType();
            $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
            $email = isset($eventData['email']) ? $eventData['email'] : '';
            $storeId = isset($eventData['store_id']) ? $eventData['store_id'] : 0;
            if (!$this->config->isTestModeEnabled($storeId) && $email) {
                $this->unsubscribeService->unsubscribeFromEventType($eventType, $email, $storeId);
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribeFromAll($securityCode)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::SECURITY_CODE, $securityCode, 'eq');

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($result->getItems() as $eventQueueItem) {
            $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
            $email = isset($eventData['email']) ? $eventData['email'] : '';
            $storeId = isset($eventData['store_id']) ? $eventData['store_id'] : 0;
            if (!$this->config->isTestModeEnabled($storeId) && $email) {
                $this->unsubscribeService->unsubscribeFromAll($email, $storeId);
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function changeCampaign($eventId, $campaignId)
    {
        /** @var CampaignInterface $campaign */
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign) {
            throw new LocalizedException(__('Campaign can not be found!'));
        }

        /** @var EventInterface $event */
        $event = $this->getEvent($eventId);
        if (!$event) {
            throw new LocalizedException(__('Event can not be found!'));
        }

        $event->setCampaignId($campaignId);
        $event = $this->eventRepository->save($event);

        return $event;
    }

    /**
     * Get campaign
     *
     * @param int $campaignId
     * @return CampaignInterface|false
     */
    private function getCampaign($campaignId)
    {
        try {
            /** @var CampaignInterface $campaign */
            $campaign = $this->campaignRepository->get($campaignId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return $campaign;
    }

    /**
     * Get event
     *
     * @param int $eventId
     * @return EventInterface|false
     */
    private function getEvent($eventId)
    {
        try {
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($eventId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return $event;
    }
}
