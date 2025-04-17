<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Model\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\DataObject\Copy;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\AdminOrder\Product;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Create
 * @package Mageplaza\EditOrder\Model\Order
 */
class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $dataObjectConverter;

    /**
     * Create constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     * @param Registry $coreRegistry
     * @param Config $salesConfig
     * @param Quote $quoteSession
     * @param LoggerInterface $logger
     * @param Copy $objectCopyService
     * @param MessageManager $messageManager
     * @param Product\Quote\Initializer $quoteInitializer
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param FormFactory $metadataFormFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param EmailSender $emailSender
     * @param StockRegistryInterface $stockRegistry
     * @param Item\Updater $quoteItemUpdater
     * @param Factory $objectFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterfaceFactory $customerFactory
     * @param Mapper $customerMapper
     * @param CartManagementInterface $quoteManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param OrderManagementInterface $orderManagement
     * @param QuoteFactory $quoteFactory
     * @param Json|null $serializer
     * @param ExtensibleDataObjectConverter|null $dataObjectConverter
     * @param StoreManagerInterface|null $storeManager
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        Registry $coreRegistry,
        Config $salesConfig,
        Quote $quoteSession,
        LoggerInterface $logger,
        Copy $objectCopyService,
        MessageManager $messageManager,
        Product\Quote\Initializer $quoteInitializer,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        FormFactory $metadataFormFactory,
        GroupRepositoryInterface $groupRepository,
        ScopeConfigInterface $scopeConfig,
        EmailSender $emailSender,
        StockRegistryInterface $stockRegistry,
        Item\Updater $quoteItemUpdater,
        Factory $objectFactory,
        CartRepositoryInterface $quoteRepository,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerFactory,
        Mapper $customerMapper,
        CartManagementInterface $quoteManagement,
        DataObjectHelper $dataObjectHelper,
        OrderManagementInterface $orderManagement,
        QuoteFactory $quoteFactory,
        Json $serializer = null,
        ExtensibleDataObjectConverter $dataObjectConverter = null,
        StoreManagerInterface $storeManager = null,
        array $data = []
    ) {
        $this->dataObjectConverter = $dataObjectConverter ?: ObjectManager::getInstance()->get(ExtensibleDataObjectConverter::class);

        parent::__construct(
            $objectManager,
            $eventManager,
            $coreRegistry,
            $salesConfig,
            $quoteSession,
            $logger,
            $objectCopyService,
            $messageManager,
            $quoteInitializer,
            $customerRepository,
            $addressRepository,
            $addressFactory,
            $metadataFormFactory,
            $groupRepository,
            $scopeConfig,
            $emailSender,
            $stockRegistry,
            $quoteItemUpdater,
            $objectFactory,
            $quoteRepository,
            $accountManagement,
            $customerFactory,
            $customerMapper,
            $quoteManagement,
            $dataObjectHelper,
            $orderManagement,
            $quoteFactory,
            $data,
            $serializer,
            $dataObjectConverter,
            $storeManager
        );
    }

    /**
     * Initialize creation data from existing order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initFromOrder(\Magento\Sales\Model\Order $order)
    {
        $session = $this->getSession();
        $session->setData($order->getReordered() ? 'reordered' : 'order_id', $order->getId());
        $session->setCurrencyId($order->getOrderCurrencyCode());
        /* Check if we edit guest order */
        $session->setCustomerId($order->getCustomerId() ?: false);
        $session->setStoreId($order->getStoreId());
        if ($session->getData('reordered')) {
            $this->getQuote()->setCustomerGroupId($order->getCustomerGroupId());
        }

        /* Initialize catalog rule data with new session values */
        $this->initRuleData();

        foreach ($order->getItemsCollection($this->_salesConfig->getAvailableProductTypes(), true) as $orderItem) {
            /* @var $orderItem \Magento\Sales\Model\Order\Item */
            if (!$orderItem->getParentItem()) {
                $qty = $orderItem->getQtyOrdered();

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($item));
                    }
                }
            }
        }

        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress) {
            $shippingAddress->setSameAsBilling($this->isAddressesAreEqual($order));
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        $quote = $this->getQuote();


        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $quote->getBillingAddress()->setCustomerAddressId(
                $quote->getShippingAddress()->getCustomerAddressId()
            );
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $quote->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $quote->setCouponCode($orderCouponCode);
        }

        if ($quote->getCouponCode()) {
            $quote->collectTotals();
        }

        $this->_objectCopyService->copyFieldsetToTarget('sales_copy_order', 'to_edit', $order, $quote);

        $this->_eventManager->dispatch('sales_convert_order_to_quote', ['order' => $order, 'quote' => $quote]);

        if (!$order->getCustomerId()) {
            $quote->setCustomerIsGuest(true);
        }

        if ($session->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        $quote->getShippingAddress()->unsCachedItemsAll();
        $quote->getBillingAddress()->unsCachedItemsAll();
        $quote->setTotalsCollectedFlag(false);

        $this->quoteRepository->save($quote);

        return $this;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isAddressesAreEqual(Order $order)
    {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress  = $order->getBillingAddress();
        $shippingData    = $this->dataObjectConverter->toFlatArray($shippingAddress, [], OrderAddressInterface::class);
        $billingData     = $this->dataObjectConverter->toFlatArray($billingAddress, [], OrderAddressInterface::class);

        unset(
            $shippingData['address_type'],
            $shippingData['entity_id'],
            $billingData['address_type'],
            $billingData['entity_id']
        );

        if (isset($shippingData['customer_address_id']) && !isset($billingData['customer_address_id'])) {
            unset($shippingData['customer_address_id']);
        }

        return $shippingData == $billingData;
    }
}
