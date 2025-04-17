<?php
namespace Aheadworks\Followupemail2\Observer;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Item as WishlistItem;

/**
 * Class WishlistAddProductEventObserver
 * @package Aheadworks\Followupemail2\Observer
 */
class WishlistAddProductEventObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EventHistoryManagementInterface
     */
    private $eventHistoryManagement;

    /**
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param EventHistoryManagementInterface $eventHistoryManagement
     */
    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        EventHistoryManagementInterface $eventHistoryManagement
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->eventHistoryManagement = $eventHistoryManagement;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isEnabled()) {
            $event = $observer->getEvent();
            /** @var Wishlist $wishlist */
            $wishlist = $event->getWishlist();
            /** @var WishlistItem $wishlistItem */
            $wishlistItem = $event->getItem();

            if ($wishlist && $wishlist->getCustomerId() && $wishlistItem && $wishlistItem->getProductId()) {
                try {
                    /** @var CustomerInterface $customer */
                    $customer = $this->customerRepository->getById($wishlist->getCustomerId());
                    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerGroupId = $customer->getGroupId();

                    $wishlistData = array_merge($wishlist->getData(), [
                        'email'             => $customer->getEmail(),
                        'customer_name'     => $customerName,
                        'customer_group_id' => $customerGroupId,
                        'wishlist_item_id'  => $wishlistItem->getId(),
                        'store_id'          => $wishlistItem->getStoreId(),
                    ]);

                    $this->eventHistoryManagement->addEvent(
                        EventInterface::TYPE_WISHLIST_CONTENT_CHANGED,
                        $wishlistData
                    );
                } catch (NoSuchEntityException $e) {
                    // do nothing
                }
            }
        }

        return $this;
    }
}
