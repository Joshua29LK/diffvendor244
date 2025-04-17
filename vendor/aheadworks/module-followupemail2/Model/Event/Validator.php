<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Order\Quote as OrderQuote;
use Aheadworks\Followupemail2\Model\Order\QuoteFactory as OrderQuoteFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote as OrderQuotResource;
use Magento\Framework\App\Area;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection as SubscriberCollection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Review\Model\Review;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class Validator
 * @package Aheadworks\Followupemail2\Model\Event
 */
class Validator
{
    /**
     * Const for 'all' value
     */
    const ALL_VALUE = 'all';

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var OrderQuotResource
     */
    private $orderQuotResource;

    /**
     * @var OrderQuoteFactory
     */
    private $orderQuoteFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartConditionConverter
     */
    private $cartConditionConverter;

    /**
     * @var ProductConditionConverter
     */
    private $productConditionConverter;

    /**
     * @var LifetimeConditionConverter
     */
    private $lifetimeConditionConverter;

    /**
     * @var SubscriberCollectionFactory
     */
    private $subscriberCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartInterfaceFactory
     */
    private $cartInterfaceFactory;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @var AwCustomerSegmentsCondition
     */
    private $awCustomerSegmentsCondition;

    /**
     * @param TypePool $eventTypePool
     * @param OrderQuotResource $orderQuotResource
     * @param OrderQuoteFactory $orderQuoteFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CartConditionConverter $cartConditionConverter
     * @param ProductConditionConverter $productConditionConverter
     * @param LifetimeConditionConverter $lifetimeConditionConverter
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterfaceFactory $cartInterfaceFactory
     * @param AppEmulation $appEmulation
     * @param AwCustomerSegmentsCondition $awCustomerSegmentsCondition
     */
    public function __construct(
        EventTypePool $eventTypePool,
        OrderQuotResource $orderQuotResource,
        OrderQuoteFactory $orderQuoteFactory,
        ProductRepositoryInterface $productRepository,
        CartConditionConverter $cartConditionConverter,
        ProductConditionConverter $productConditionConverter,
        LifetimeConditionConverter $lifetimeConditionConverter,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        CartRepositoryInterface $cartRepository,
        CartInterfaceFactory $cartInterfaceFactory,
        AppEmulation $appEmulation,
        AwCustomerSegmentsCondition $awCustomerSegmentsCondition
    ) {
        $this->eventTypePool = $eventTypePool;
        $this->orderQuotResource = $orderQuotResource;
        $this->orderQuoteFactory = $orderQuoteFactory;
        $this->productRepository = $productRepository;
        $this->cartConditionConverter = $cartConditionConverter;
        $this->productConditionConverter = $productConditionConverter;
        $this->lifetimeConditionConverter = $lifetimeConditionConverter;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->cartInterfaceFactory = $cartInterfaceFactory;
        $this->appEmulation = $appEmulation;
        $this->awCustomerSegmentsCondition = $awCustomerSegmentsCondition;
    }

    /**
     * Validate event data by specified event
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param mixed $eventObject
     * @return bool
     */
    public function validate(EventInterface $event, $eventData, $eventObject)
    {
        $result = true;
        if (!$this->validateReceiver($event, $eventData)) {
            $result = false;
        }

        /** @var EventTypeInterface $eventType */
        $eventType = $this->eventTypePool->getType($event->getEventType());

        if ($result && !$this->validateStores($event, $eventData)) {
            $result = false;
        }

        if ($result && $eventType->isCustomerConditionsEnabled()) {
            if (!$this->validateCustomerGroups($event, $eventData)) {
                $result = false;
            }

            if ($result && !$this->validateLifetimeCondition($event, $eventData)) {
                $result = false;
            }

            if ($result && !$this->validateAwCustomerSegments($event, $eventData)) {
                $result = false;
            }
        }

        if ($result && $eventType->isCartConditionsEnabled()) {
            if (!$this->validateCartConditions($event, $eventData, $eventObject)) {
                $result = false;
            }
        }

        if ($result && $eventType->isOrderConditionsEnabled()) {
            if (!$this->validateOrderCartConditions($event, $eventData, $eventObject)) {
                $result = false;
            }

            if ($result && !$this->validateOrderStatus($event, $eventData)) {
                $result = false;
            }
        }

        if ($result && $eventType->isProductConditionsEnabled()) {
            if ($eventType->isProductRulesEnabled()) {
                if (!$this->validateProductRules($event, $eventData, $eventObject)) {
                    $result = false;
                }
            }

            if ($result && !$this->validateProductTypes($event, $eventData, $eventObject)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Validate receiver
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateReceiver(EventInterface $event, $eventData)
    {
        if ($event->getNewsletterOnly()) {
            /** @var SubscriberCollection $subscribers */
            $subscribersCollection = $this->subscriberCollectionFactory->create();
            $storeId = $eventData['store_id'];
            $customerEmail = empty($eventData['customer_email']) ? $eventData['email'] : $eventData['customer_email'];
            $subscribersCollection
                ->addStoreFilter($storeId)
                ->addFieldToFilter('subscriber_email', $customerEmail);
            /** @var Subscriber $subscriber */
            $subscriber = $subscribersCollection->getFirstItem();
            return $subscriber && $subscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED;
        }
        return true;
    }

    /**
     * Store views validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateStores(EventInterface $event, $eventData)
    {
        return !(!in_array($eventData['store_id'], $event->getStoreIds(), false) &&
            !in_array(0, $event->getStoreIds()));
    }

    /**
     * Customer groups validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateCustomerGroups(EventInterface $event, $eventData)
    {
        return !(!in_array($eventData['customer_group_id'], $event->getCustomerGroups(), false) &&
            !in_array(self::ALL_VALUE, $event->getCustomerGroups()));
    }

    /**
     * Lifetime condition validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateLifetimeCondition(EventInterface $event, $eventData)
    {
        /** @var LifetimeCondition $lifetimeCondition */
        $lifetimeCondition = $this->lifetimeConditionConverter->getCondition($event);
        if (isset($eventData['customer_id']) && $eventData['customer_id']) {
            return $lifetimeCondition->validate($eventData['customer_id']);
        }

        return $lifetimeCondition->validate($eventData['email'], $eventData['store_id']);
    }

    /**
     * AW customer segments validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateAwCustomerSegments($event, $eventData)
    {
        $customerId = isset($eventData['customer_id']) ? $eventData['customer_id'] : null;
        $result = $this->awCustomerSegmentsCondition->validate(
            $customerId,
            $eventData['email'],
            $eventData['store_id'],
            $event->getAwCustomerSegmentIds()
        );
        return $result;
    }

    /**
     * Order Status validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @return bool
     */
    private function validateOrderStatus(EventInterface $event, $eventData)
    {
        if (!in_array(self::ALL_VALUE, $event->getOrderStatuses())) {
            if (!in_array($eventData['status'], $event->getOrderStatuses(), false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Product types validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param mixed $eventObject
     * @return bool
     */
    private function validateProductTypes(EventInterface $event, $eventData, $eventObject)
    {
        if (!in_array(self::ALL_VALUE, $event->getProductTypeIds())) {
            foreach ($eventObject->getItems() as $item) {
                if (!$item->getParentItemId() &&
                    !in_array($item->getProductType(), $event->getProductTypeIds(), false)
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Product rules (conditions) validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param Review $eventObject
     * @return bool
     */
    private function validateProductRules(EventInterface $event, $eventData, $eventObject)
    {
        if ($eventObject) {
            /** @var ProductCondition $productCondition */
            $productCondition = $this->productConditionConverter->getCondition($event);

            if ($productCondition->getConditions()->getConditions()) {
                try {
                    /** @var ProductInterface|Product $product */
                    $product = $this->productRepository->getById(
                        $eventData['product_id'],
                        false,
                        $eventData['store_id'],
                        true
                    );

                    $products[] = $product;
                    $products = array_merge($products, $this->getChildrenProducts($product, $eventData['store_id']));

                    $valid = false;
                    foreach ($products as $product) {
                        if ($productCondition->validate($product)) {
                            $valid = true;
                            break;
                        }
                    }
                    if (!$valid) {
                        return false;
                    }
                } catch (NoSuchEntityException $e) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Cart conditions validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param CartInterface|Quote $eventObject
     * @return bool
     */
    private function validateCartConditions(EventInterface $event, $eventData, $eventObject)
    {
        if ($eventObject) {
            /** @var CartCondition $cartCondition */
            $cartCondition = $this->cartConditionConverter->getCondition($event);

            if ($cartCondition->getConditions()->getConditions()) {
                if ($eventObject->isVirtual()) {
                    $address = $eventObject->getBillingAddress();
                } else {
                    $address = $eventObject->getShippingAddress();
                }

                foreach ($address->getAllItems() as $item) {
                    /** @var ProductInterface|Product $product */
                    $product = $this->productRepository->getById(
                        $item->getProductId(),
                        false,
                        $eventData['store_id'],
                        true
                    );
                    $item->setProduct($product);
                }
                if ($cartCondition->getConditions()->getConditions()) {
                    $eventObject->collectTotals();
                    if (!$cartCondition->validate($address)) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Order conditions validation
     *
     * @param EventInterface $event
     * @param array $eventData
     * @param OrderInterface|Order $eventObject
     * @return bool
     */
    private function validateOrderCartConditions(EventInterface $event, $eventData, $eventObject)
    {
        if ($eventObject) {
            /** @var CartInterface|Quote $cart */
            $cart = $this->getQuote($eventObject->getQuoteId(), $eventObject->getId(), $eventObject->getStoreId());

            /** @var CartCondition $cartCondition */
            $cartCondition = $this->cartConditionConverter->getCondition($event);

            if ($cartCondition->getConditions()->getConditions()) {
                if ($cart->isVirtual()) {
                    $address = $cart->getBillingAddress();
                } else {
                    $address = $cart->getShippingAddress();
                }

                foreach ($address->getAllItems() as $item) {
                    /** @var ProductInterface|Product $product */
                    $product = $this->productRepository->getById(
                        $item->getProductId(),
                        false,
                        $eventData['store_id'],
                        true
                    );
                    $item->setProduct($product);
                }
                if ($cartCondition->getConditions()->getConditions()) {
                    $cart->collectTotals();
                    $address
                        ->setTotalQty($eventObject->getTotalQtyOrdered())
                        ->setPaymentMethod($eventObject->getPayment()->getMethod());
                    if (!$cartCondition->validate($address)) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Get quote
     *
     * @param int $quoteId
     * @param int $orderId
     * @param int $storeId
     * @return CartInterface|Quote
     */
    private function getQuote($quoteId, $orderId, $storeId)
    {
        try {
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            /** @var CartInterface|Quote $cart */
            $cart = $this->cartRepository->get($quoteId);
            $this->appEmulation->stopEnvironmentEmulation();
        } catch (\Exception $e) {
            $this->appEmulation->stopEnvironmentEmulation();
            /** @var CartInterface|Quote $cart */
            $cart = $this->cartInterfaceFactory->create();

            /** @var OrderQuote $orderQuote */
            $orderQuote = $this->orderQuoteFactory->create();
            $this->orderQuotResource->load($orderQuote, $orderId, 'order_id');

            if ($orderQuote->getId()) {
                $cart->setData($orderQuote->getQuoteData());
            }
        }
        return $cart;
    }

    /**
     * Get children products
     *
     * @param ProductInterface $product
     * @param int|null $storeId
     * @return array
     */
    private function getChildrenProducts($product, $storeId = null)
    {
        $childIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $childProducts = [];
        foreach ($childIds as $group => $groupChildIds) {
            foreach ($groupChildIds as $childId) {
                try {
                    $childProduct = $this->productRepository->getById(
                        $childId,
                        false,
                        $storeId,
                        true
                    );
                    $childProducts[] = $childProduct;
                } catch (NoSuchEntityException $e) {
                    // skip
                }
            }
        }
        return $childProducts;
    }
}
