<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class NewsletterSubscription
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class NewsletterSubscription extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_NEWSLETTER_SUBSCRIPTION;

    /**
     * @var string
     */
    protected $referenceDataKey = 'subscriber_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'subscriber';

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        SubscriberFactory $subscriberFactory
    ) {
        parent::__construct(
            $campaignManagement,
            $eventRepository,
            $eventHistoryRepository,
            $eventValidator,
            $eventQueueManagement,
            $searchCriteriaBuilder,
            $serializer
        );
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        $eventdata = $this->serializer->unserialize($eventHistoryItem->getEventData());
        /** @var Subscriber $subscriber */
        $subscriber = $this->getEventObject($eventdata);
        if (!$subscriber || $subscriber->getSubscriberStatus() != Subscriber::STATUS_SUBSCRIBED) {
            $this->eventHistoryRepository->delete($eventHistoryItem);
        } else {
            parent::process($eventHistoryItem);
        }
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Subscriber|null
     */
    public function getEventObject($eventData)
    {
        $subscriber = $this->subscriberFactory->create()->load($eventData[$this->getReferenceDataKey()]);
        if (!$subscriber->getId()) {
            return null;
        }

        return $subscriber;
    }
}
