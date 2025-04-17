<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class OrderStatusChanged
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class OrderStatusChanged extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_ORDER_STATUS_CHANGED;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'order';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        OrderRepositoryInterface $orderRepository
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

        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEvents($eventCode, $data = [])
    {
        /** @var OrderInterface $order */
        $order = $this->getEventObject($data);

        $quoteId = $order->getQuoteId();
        $this->eventQueueManagement->cancelEvents(
            EventInterface::TYPE_ABANDONED_CART,
            $quoteId
        );

        parent::cancelEvents($eventCode, $data);
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return OrderInterface|Order|null
     */
    public function getEventObject($eventData)
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($eventData[$this->getReferenceDataKey()]);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $order;
    }
}
