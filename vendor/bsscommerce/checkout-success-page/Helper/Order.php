<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Order extends AbstractHelper
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Block\Order\Recent
     */
    protected $reorder;

    /**
     * @var \Magento\Sales\Block\Order\Info\Buttons
     */
    protected $print;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Block\Order\Recent $reorder
     * @param \Magento\Sales\Block\Order\Info\Buttons $print
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Block\Order\Recent $reorder,
        \Magento\Sales\Block\Order\Info\Buttons $print
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reorder = $reorder;
        $this->print = $print;
    }

    /**
     * @return int
     */
    public function getOrderIdFromConfig()
    {
        return $this->scopeConfig->getValue(
            'checkoutsuccesspage/preview/order_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    protected function getOrderCollection()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addSortOrder('created_at', AbstractCollection::SORT_ORDER_DESC);
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchCriteria->setPageSize(1);
        $searchCriteria->setCurrentPage(0);
        $searchCriteria->getSortOrders();

        $orders = $this->orderRepository->getList($searchCriteria);

        return $orders;
    }

    public function getItemById($id)
    {
        return $this->productRepositoryFactory->create()->getById($id);
    }

    /**
     * @return mixed || bool
     */
    public function getOrder()
    {
        if ($this->checkoutSession->getLastRealOrderId()) {
            $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
            return $order;
        }
        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getOrderController()
    {

        $orderIdFromConfig = $this->getOrderIdFromConfig();
        if ($orderIdFromConfig) {
            $order = $this->loadOrder($orderIdFromConfig);
            if ($order) {
                return $order;
            }
        }

        $lastOrder = $this->getOrderCollection()->getLastItem();

        if ($lastOrder) {
            return $lastOrder;
        }

        return false;
    }

    /**
     * @param int $orderId
     * @return bool|mixed
     */
    public function loadOrder($orderId)
    {
        $order = $this->getOrderById($orderId);
        if ($order !== false && $order->getId() > 0) {
            return $order;
        }

        return false;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrderById($orderId)
    {
        if (empty($orderId)) {
            return false;
        }
        try {
            return $this->orderFactory->create()->loadByIncrementId($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * @param $order
     */
    public function registerOrder($order)
    {

        $currentOrder = $this->registry->registry('current_order');
        if (empty($currentOrder)) {
            $this->registry->register('current_order', $order);
        }
        $this->checkoutSession->setLastOrderId($order->getId())
        ->setLastRealOrderId($order->getIncrementId());
    }

    /**
     * @param $order
     * @return string
     */
    public function getReorder($order)
    {
        return $this->reorder->getReorderUrl($order);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrint($order)
    {
        return $this->print->getPrintUrl($order);
    }
}
