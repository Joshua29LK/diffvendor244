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
use Magento\Framework\App\Area;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AbandonedCart
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class AbandonedCart extends AbstractHandler
{
    /**
     * @var string
     */
    const CART_TRIGGER_TIMEOUT = 3600;

    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_ABANDONED_CART;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'quote';

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param CartRepositoryInterface $cartRepository
     * @param AppEmulation $appEmulation
     * @param DateTime $dateTime
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        CartRepositoryInterface $cartRepository,
        AppEmulation $appEmulation,
        DateTime $dateTime
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

        $this->cartRepository = $cartRepository;
        $this->appEmulation = $appEmulation;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        $eventdata = $this->serializer->unserialize($eventHistoryItem->getEventData());
        /** @var CartInterface|Quote $cart */
        $cart = $this->getEventObject($eventdata);
        if (!$cart
            || !$cart->getIsActive()
            || $cart->getItemsCount() == 0
        ) {
            $this->eventHistoryRepository->delete($eventHistoryItem);
        } else {
            $triggerAt = $this->dateTime->timestamp($eventHistoryItem->getTriggeredAt());
            $now = $this->dateTime->timestamp();
            if ($now - $triggerAt > self::CART_TRIGGER_TIMEOUT) {
                 parent::process($eventHistoryItem);
            }
        }
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return CartInterface|Quote|null
     */
    public function getEventObject($eventData)
    {
        try {
            $this->appEmulation->startEnvironmentEmulation($eventData['store_id'], Area::AREA_FRONTEND, true);
            /** @var CartInterface|Quote $cart */
            $cart = $this->cartRepository->get($eventData[$this->getReferenceDataKey()]);
            $this->appEmulation->stopEnvironmentEmulation();
        } catch (NoSuchEntityException $e) {
            $this->appEmulation->stopEnvironmentEmulation();
            return null;
        }
        return $cart;
    }
}
