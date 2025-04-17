<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Order\Quote as OrderQuote;
use Aheadworks\Followupemail2\Model\Order\QuoteFactory as OrderQuoteFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote as OrderQuoteResource;
use Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote\Collection as OrderQuoteCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Order\Quote\CollectionFactory as OrderQuoteCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Order
 * @package Aheadworks\Followupemail2\Model\Plugin
 * @codeCoverageIgnore
 */
class Order
{
    /**
     * @var EventHistoryManagementInterface
     */
    private $eventHistoryManagement;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]|null
     */
    private $orderStatus;

    /**
     * @var OrderQuoteFactory
     */
    private $orderQuoteFactory;

    /**
     * @var OrderQuoteResource
     */
    private $orderQuoteResource;

    /**
     * @var OrderQuoteCollectionFactory
     */
    private $orderQuoteCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param Config $config
     * @param OrderQuoteFactory $orderQuoteFactory
     * @param OrderQuoteResource $orderQuoteResource
     * @param OrderQuoteCollectionFactory $orderQuoteCollectionFactory
     * @param CartRepositoryInterface $cartRepository
     * @param AppEmulation $appEmulation
     */
    public function __construct(
        EventHistoryManagementInterface $eventHistoryManagement,
        Config $config,
        OrderQuoteFactory $orderQuoteFactory,
        OrderQuoteResource $orderQuoteResource,
        OrderQuoteCollectionFactory $orderQuoteCollectionFactory,
        CartRepositoryInterface $cartRepository,
        AppEmulation $appEmulation
    ) {
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->config = $config;
        $this->orderQuoteFactory = $orderQuoteFactory;
        $this->orderQuoteResource = $orderQuoteResource;
        $this->orderQuoteCollectionFactory = $orderQuoteCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Store order status
     *
     * @param OrderResource $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order $order
     * @param string $value
     * @param null $field
     * @return OrderResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoad(
        OrderResource $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order $order,
        $value,
        $field = null
    ) {
        $result = $proceed($order, $value, $field);

        if ($order->getId()) {
            $this->orderStatus[$order->getId()] = $order->getStatus();
        }

        return $result;
    }

    /**
     * Add order to event history
     *
     * @param OrderResource $subject,
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order $order
     * @return OrderResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        OrderResource $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order $order
    ) {
        $orderIdBeforeSave = $order->getId();
        $isNewOrder = empty($orderIdBeforeSave);

        $result = $proceed($order);

        if ($order->getId() && $order->getQuoteId()) {
            $this->saveOrderQuote($order);
        }

        if ($this->config->isEnabled()) {
            if ($order->getId()
                && ((isset($this->orderStatus[$order->getId()])
                        && $this->orderStatus[$order->getId()] != $order->getStatus())
                    || ($order->getStatus() && !isset($this->orderStatus[$order->getId()]))
                    || ($order->getStatus() && $order->getStatus() != $order->getOrigData(OrderInterface::STATUS))
                    || ($isNewOrder)
                )
            ) {
                if ($order->getCustomerId()) {
                    $customerFirstName = $order->getCustomerFirstname();
                    $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                } else {
                    $customerFirstName = $order->getBillingAddress() ?
                        $order->getBillingAddress()->getFirstname() : null;
                    $customerName = $order->getBillingAddress() ?
                        $order->getBillingAddress()->getName() : null;
                }
                $orderData = array_merge(
                    $order->getData(),
                    [
                        'email' => $order->getCustomerEmail(),
                        'customer_firstname' => $customerFirstName,
                        'customer_name' => $customerName
                    ]
                );
                $this->eventHistoryManagement->addEvent(EventInterface::TYPE_ORDER_STATUS_CHANGED, $orderData);
            }
        }

        return $result;
    }

    /**
     * Save order quote
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    private function saveOrderQuote($order)
    {
        if (!$this->isQuoteForOrderAlreadySaved($order)) {
            $cart = $this->getCartForOrder($order);
            if ($cart) {
                try {
                    /** @var OrderQuote $orderQuote */
                    $orderQuote = $this->orderQuoteFactory->create();
                    $orderQuote
                        ->setOrderId($order->getId())
                        ->setQuoteData($cart->getData());
                    $this->orderQuoteResource->save($orderQuote);
                    return true;
                } catch (\Exception $exception) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve cart for sales order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return Quote|CartInterface
     */
    private function getCartForOrder($order)
    {
        try {
            $this->appEmulation->startEnvironmentEmulation(
                $order->getStoreId(),
                Area::AREA_FRONTEND,
                true
            );
            /** @var CartInterface|Quote $cart */
            $cart = $this->cartRepository->get($order->getQuoteId());
            $this->appEmulation->stopEnvironmentEmulation();
        } catch (\Exception $exception) {
            $cart = null;
        }
        return $cart;
    }

    /**
     * Check if quote for order is already saved
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    private function isQuoteForOrderAlreadySaved($order)
    {
        /** @var OrderQuoteCollection $orderQuoteCollection */
        $orderQuoteCollection = $this->orderQuoteCollectionFactory->create();
        $orderQuoteCollection->addFilterByOrderId($order->getId());
        $orderQuoteId = $orderQuoteCollection->getFirstItem()->getId();
        return isset($orderQuoteId);
    }
}
