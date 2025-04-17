<?php
namespace Aheadworks\Followupemail2\Model\Template;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface as EventHandlerInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class Variable
 * @package Aheadworks\Followupemail2\Model\Template
 */
class Variable
{
    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var WishlistCollectionFactory
     */
    private $wishlistCollectionFactory;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EventTypePool $eventTypePool
     * @param ObjectManagerInterface $objectManager
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param ReviewFactory $reviewFactory
     * @param WishlistCollectionFactory $wishlistCollectionFactory
     * @param WishlistFactory $wishlistFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        EventTypePool $eventTypePool,
        ObjectManagerInterface $objectManager,
        QuoteCollectionFactory $quoteCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        ReviewFactory $reviewFactory,
        WishlistCollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Serializer $serializer
    ) {
        $this->eventTypePool = $eventTypePool;
        $this->objectManager = $objectManager;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->serializer = $serializer;
    }

    /**
     * Get test variable data
     *
     * @param int $storeId
     * @return array
     */
    public function getTestVariableData($storeId)
    {
        $emailData = [];

        // Create quote instance
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection
            ->addFilter('is_active', 1)
            ->addFieldToFilter('items_count', ['gt' => 0])
            ->getSelect()
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit(1)
        ;
        /** @var Quote $quote */
        $quote = $quoteCollection->getFirstItem();
        if ($quote) {
            $emailData['quote'] = $quote;
        }

        // Create order instance
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFilter('state', 'complete')
            ->getSelect()
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit(1)
        ;
        /** @var Order $order */
        $order = $orderCollection->getFirstItem();
        if ($order) {
            $emailData['order'] = $order;
        }

        // Create customer instance
        if ($order && $order->getCustomerId()) {
            $customerCollection = $this->customerCollectionFactory->create();
            $customerCollection->addFilter($customerCollection->getRowIdFieldName(), $order->getCustomerId());
            /** @var Customer $customer */
            $customer = $customerCollection->getFirstItem();
        } else {
            $customerCollection = $this->customerCollectionFactory->create();
            $customerCollection->getSelect()
                ->order(new \Zend_Db_Expr('RAND()'))
                ->limit(1);
            /** @var Customer $customer */
            $customer = $customerCollection->getFirstItem();
        }

        if ($customer->getId()) {
            $emailData['customer'] = $customer;
            $storeId = $customer->getStoreId();
        }

        // Create store instance
        $emailData['store'] = $this->storeManager->getStore($storeId);
        // Add required event data
        $customerData = [
            'email'  => $customer->getEmail(),
            'store_id'  => $storeId,
            'customer_group_id'  => $customer->getGroupId(),
            'customer_firstname' => $customer->getFirstname(),
            'customer_name' => $customer->getName()
        ];

        $emailData['url_unsubscribe_all'] = $this->urlBuilder
            ->setScope($this->storeManager->getStore($storeId))
            ->getUrl(
                'aw_followupemail2/unsubscribe/all',
                [
                    'code' => 'TEST',
                    '_scope_to_url' => true,
                ]
            );
        $emailData['url_unsubscribe_event_type'] = $this->urlBuilder
            ->setScope($this->storeManager->getStore($storeId))
            ->getUrl(
                'aw_followupemail2/unsubscribe/eventType',
                [
                    'code' => 'TEST',
                    '_scope_to_url' => true,
                ]
            );
        $emailData['url_unsubscribe_event'] = $this->urlBuilder
            ->setScope($this->storeManager->getStore($storeId))
            ->getUrl(
                'aw_followupemail2/unsubscribe/event',
                [
                    'code' => 'TEST',
                    '_scope_to_url' => true,
                ]
            );
        $emailData['url_restore_cart'] = $this->urlBuilder
            ->setScope($this->storeManager->getStore($storeId))
            ->getUrl(
                'aw_followupemail2/cart/restore',
                [
                    'code' => 'TEST',
                    '_scope_to_url' => true,
                ]
            );

        $emailData['review'] = $this->getTestReview();
        $emailData['wishlist'] = $this->getTestWishlist();

        return array_merge($emailData, $order->getData(), $customerData);
    }

    /**
     * Get test review
     *
     * @return Review
     */
    private function getTestReview()
    {
        /** @var Review $review */
        $review = $this->reviewFactory->create();
        $review->setReviewId(1);
        $review->setNickname('Review Nickname');
        $review->setTitle('Review Title');
        $review->setDetail('Review Detail');

        return $review;
    }

    /**
     * Get test wishlist
     *
     * @return Wishlist
     */
    private function getTestWishlist()
    {
        /** @var WishlistCollection $wishlistCollection */
        $wishlistCollection = $this->wishlistCollectionFactory->create();
        $wishlistCollection
            ->addNotEmptyFilter()
            ->getSelect()
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit(1);

        /** @var Wishlist $wishlist */
        $wishlist = $wishlistCollection->getFirstItem();

        return $wishlist;
    }

    /**
     * Get variable data
     *
     * @param EventQueueInterface $eventQueueItem
     * @return array
     * @throws \Exception
     */
    public function getVariableData(EventQueueInterface $eventQueueItem)
    {
        try {
            $emailData = $this->serializer->unserialize($eventQueueItem->getEventData());
            /** @var EventTypeInterface $type */
            $type = $this->eventTypePool->getType($eventQueueItem->getEventType());
            /** @var EventHandlerInterface $eventHandler */
            $eventHandler = $type->getHandler();

            $eventObject = $eventHandler->getEventObject($emailData);
            if ($eventObject) {
                $emailData[$eventHandler->getEventObjectVariableName()] = $eventObject;
                $storeId = $eventObject->getStoreId();
                if (!$storeId) {
                    $storeId = 0;
                }
            } else {
                throw new \Exception(__("Event object is missing"));
            }

            if (isset($emailData['customer_id'])) {
                $customerCollection = $this->customerCollectionFactory->create();
                $customerCollection->addFilter($customerCollection->getRowIdFieldName(), $emailData['customer_id']);
                /** @var Customer $customer */
                $customer = $customerCollection->getFirstItem();
                if ($customer->getId()) {
                    $emailData['customer'] = $customer;
                }
            }

            if (isset($emailData['customer'])) {
                $storeId = $emailData['customer']->getStoreId();
                $emailData['store'] = $this->storeManager->getStore($emailData['customer']->getStoreId());
            } else {
                $emailData['store'] = $this->storeManager->getStore($storeId);
            }

            $emailData['url_unsubscribe_all'] = $this->urlBuilder
                ->setScope($this->storeManager->getStore($storeId))
                ->getUrl(
                    'aw_followupemail2/unsubscribe/all',
                    [
                        'code' => $eventQueueItem->getSecurityCode(),
                        '_scope_to_url' => true,
                    ]
                );
            $emailData['url_unsubscribe_event_type'] = $this->urlBuilder
                ->setScope($this->storeManager->getStore($storeId))
                ->getUrl(
                    'aw_followupemail2/unsubscribe/eventType',
                    [
                        'code' => $eventQueueItem->getSecurityCode(),
                        '_scope_to_url' => true,
                    ]
                );
            $emailData['url_unsubscribe_event'] = $this->urlBuilder
                ->setScope($this->storeManager->getStore($storeId))
                ->getUrl(
                    'aw_followupemail2/unsubscribe/event',
                    [
                        'code' => $eventQueueItem->getSecurityCode(),
                        '_scope_to_url' => true,
                    ]
                );
            $emailData['url_restore_cart'] = $this->urlBuilder
                ->setScope($this->storeManager->getStore($storeId))
                ->getUrl(
                    'aw_followupemail2/cart/restore',
                    [
                        'code' => $eventQueueItem->getSecurityCode(),
                        '_scope_to_url' => true,
                    ]
                );
        } catch (NoSuchEntityException $e) {
            $emailData = [];
        }
        return $emailData;
    }
}
