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
use Magento\Framework\App\Area;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\Item as WishlistItem;

/**
 * Class WishlistContentChanged
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class WishlistContentChanged extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_CUSTOMER_REVIEW;

    /**
     * @var string
     */
    protected $referenceDataKey = 'wishlist_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'wishlist';

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param WishlistFactory $wishlistFactory
     * @param AppEmulation $appEmulation
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        WishlistFactory $wishlistFactory,
        AppEmulation $appEmulation
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
        $this->wishlistFactory = $wishlistFactory;
        $this->appEmulation = $appEmulation;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        $eventData = $this->serializer->unserialize($eventHistoryItem->getEventData());
        /** @var Wishlist $wishlist */
        $wishlist = $this->getEventObject($eventData);
        $this->appEmulation->startEnvironmentEmulation($eventData['store_id'], Area::AREA_FRONTEND, true);
        if ($wishlist && $this->isWishlistItemExists($wishlist, $eventData)) {
            /** @var EventInterface[] $events */
            $events = $this->validate($eventHistoryItem);

            if (count($events) > 0) {
                /** @var EventInterface $event */
                foreach ($events as $event) {
                    $this->eventQueueManagement->cancelEventsByEventId($event->getId(), $wishlist->getId());

                    /** @var EventQueueInterface $queueItem */
                    $this->eventQueueManagement->add($event, $eventHistoryItem, false);
                }
                $eventHistoryItem->setProcessed(true);
                $this->eventHistoryRepository->save($eventHistoryItem);
            } else {
                $this->eventHistoryRepository->delete($eventHistoryItem);
            }
        } else {
            $this->eventHistoryRepository->delete($eventHistoryItem);
        }
        $this->appEmulation->stopEnvironmentEmulation();
    }

    /**
     * Check if wishlist item exists
     *
     * @param Wishlist $wishlist
     * @param array $eventData
     * @return bool
     */
    private function isWishlistItemExists($wishlist, $eventData = [])
    {
        $wishlist->setSharedStoreIds([$eventData['store_id']]);
        /** @var WishlistItem $wishlistItem */
        $wishlistItem = $wishlist->getItem($eventData['wishlist_item_id']);

        if ($wishlistItem) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function validate(EventHistoryInterface $eventHistoryItem)
    {
        $events = [];
        $eventData = $this->serializer->unserialize($eventHistoryItem->getEventData());
        /** @var Wishlist $wishlist */
        $wishlist = $this->getEventObject($eventData);

        /** @var EventInterface $event */
        foreach ($this->getEventsForValidation($eventHistoryItem->getEventType()) as $event) {
            if ($this->isWishlistItemValid($event, $eventData, $wishlist)) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Is wishlist item valid
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param Wishlist $wishlist
     * @return bool
     */
    private function isWishlistItemValid($event, $eventData, $wishlist)
    {
        $wishlist->setSharedStoreIds([$eventData['store_id']]);
        /** @var WishlistItem $item */
        $item = $wishlist->getItem($eventData['wishlist_item_id']);
        $productId = $item->getProductId();
        $itemData = array_merge($eventData, ['product_id' => $productId]);
        if ($this->eventValidator->validate($event, $itemData, $wishlist)) {
            return true;
        }
        return false;
    }

    /**
     * Is wishlist specified valid
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param Wishlist $wishlist
     * @return bool
     */
    private function isWishlistValid($event, $eventData, $wishlist)
    {
        $wishlist->setSharedStoreIds([$eventData['store_id']]);
        /** @var Wishlist $items */
        $items = $wishlist->getItemCollection();
        /** @var WishlistItem $item */
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $itemData = array_merge($eventData, ['product_id' => $productId]);
            if ($this->eventValidator->validate($event, $itemData, $wishlist)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Wishlist|null
     */
    public function getEventObject($eventData)
    {
        $wishlist = $this->wishlistFactory->create();
        $wishlist->load($eventData[$this->getReferenceDataKey()]);

        if (!$wishlist->getId()) {
            return null;
        }

        return $wishlist;
    }

    /**
     * {@inheritdoc}
     */
    public function validateEventData(array $data = [])
    {
        if (array_key_exists('delete_from_wishlist', $data)) {
            return $this->deleteFromWishlistValidation($data);
        }

        if (!array_key_exists('wishlist_item_id', $data)) {
            return false;
        }

        return parent::validateEventData($data);
    }

    /**
     * Validate the event data if an item is deleted from the wishlist
     *
     * @param array $data
     * @return bool
     */
    private function deleteFromWishlistValidation(array $data = [])
    {
        $dataKeysRequired = ['wishlist_id', 'wishlist_item_id', 'store_id', 'delete_from_wishlist'];
        foreach ($dataKeysRequired as $dataKey) {
            if (!array_key_exists($dataKey, $data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelEvents($eventCode, $data = [])
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->getEventObject($data);
        if ($wishlist) {
            /** @var EventInterface[] $events */
            $events = $this->getEventsForValidation($eventCode);

            /** @var EventInterface $event */
            foreach ($events as $event) {
                if (!$this->isWishlistValid($event, $data, $wishlist)) {
                    $this->eventQueueManagement->cancelEventsByEventId(
                        $event->getId(),
                        $data[$this->getReferenceDataKey()]
                    );
                }
            }
        } else {
            $this->eventQueueManagement->cancelEvents(
                $eventCode,
                $data[$this->getReferenceDataKey()]
            );
        }
    }
}
