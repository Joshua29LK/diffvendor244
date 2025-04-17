<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item as WishlistItemModel;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class WishlistItem
 * @package Aheadworks\Followupemail2\Model\Event\Plugin
 */
class WishlistItem
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
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param Config $config
     * @param WishlistFactory $wishlistFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        EventHistoryManagementInterface $eventHistoryManagement,
        Config $config,
        WishlistFactory $wishlistFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->config = $config;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add remove wishlist item event to event history
     *
     * @param WishlistItemResource $subject,
     * @param \Closure $proceed
     * @param WishlistItemModel $wishlistItem
     * @return WishlistItemResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        WishlistItemResource $subject,
        \Closure $proceed,
        WishlistItemModel $wishlistItem
    ) {
        $wishlistItemId = $wishlistItem->getId();
        $wishlistId = $wishlistItem->getWishlistId();
        $storeId = $wishlistItem->getStoreId();

        $result = $proceed($wishlistItem);

        if ($this->config->isEnabled()
            && $wishlistItemId
        ) {
            $wishlistData = [
                'wishlist_id' => $wishlistId,
                'wishlist_item_id' => $wishlistItemId,
                'store_id' => $storeId,
                'delete_from_wishlist' => true,
            ];

            /** @var Wishlist $wishlist */
            $wishlist = $this->wishlistFactory->create()->load($wishlistId);
            if ($wishlist->getId() && $wishlist->getCustomerId()) {
                try {
                    /** @var CustomerInterface $customer */
                    $customer = $this->customerRepository->getById($wishlist->getCustomerId());
                    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerGroupId = $customer->getGroupId();

                    $wishlistData = array_merge($wishlistData, [
                        'email'             => $customer->getEmail(),
                        'customer_name'     => $customerName,
                        'customer_group_id' => $customerGroupId,
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

        return $result;
    }
}
