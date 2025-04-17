<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CartRestorer
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class CartRestorer
{
    /**
     * Section data ids
     */
    const SECTION_DATA_IDS = 'section_data_ids';

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        EventQueueRepositoryInterface $eventQueueRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        JsonHelper $jsonHelper
    ) {
        $this->eventQueueRepository = $eventQueueRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Restore cart
     *
     * @param string $securityCode
     * @return bool
     * @throws \Exception
     */
    public function restore($securityCode)
    {
        $this->searchCriteriaBuilder
            ->addFilter(EventQueueInterface::SECURITY_CODE, $securityCode, 'eq');

        /** @var EventQueueSearchResultsInterface $result */
        $result = $this->eventQueueRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($result->getItems() as $eventQueueItem) {
            if ($eventQueueItem->getEventType() == EventInterface::TYPE_ABANDONED_CART) {
                $cartId = $eventQueueItem->getReferenceId();
                try {
                    /** @var CartInterface|Quote $cart */
                    $cart = $this->cartRepository->get($cartId);
                    /** @var CartInterface|Quote $currentCart */
                    $currentCart = $this->checkoutSession->getQuote();
                    if ($currentCart->getId() == $cart->getId()) {
                        throw new \Exception(__('Current cart can not be restored'));
                    }

                    if (!$currentCart->getItemsCount()) {
                        if ($this->customerSession->isLoggedIn()) {
                            $currentCartId = $this->cartManagement->createEmptyCartForCustomer(
                                $this->customerSession->getCustomerId()
                            );
                        } else {
                            $currentCartId = $this->cartManagement->createEmptyCart();
                        }
                        $currentCart = $this->cartRepository->get($currentCartId);
                    }

                    $currentCart->merge($cart)->collectTotals();
                    $this->cartRepository->save($currentCart);
                    $this->checkoutSession->replaceQuote($currentCart);
                    $this->invalidateTopCart();
                } catch (NoSuchEntityException $e) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Invalidate top cart section
     *
     * @return void
     */
    private function invalidateTopCart()
    {
        $sectionsJson = $this->cookieManager->getCookie(self::SECTION_DATA_IDS);
        $sections = $this->jsonHelper->jsonDecode($sectionsJson);
        if (isset($sections['cart'])) {
            $sections['cart'] += 1000;
            $sectionsJson =  $this->jsonHelper->jsonEncode($sections);
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie(self::SECTION_DATA_IDS, $metadata);
            $this->cookieManager->setPublicCookie(self::SECTION_DATA_IDS, $sectionsJson, $metadata);
        }
    }
}
