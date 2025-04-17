<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Helper;

use Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\CollectionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Bss\MultiWishlist\Model\WishlistLabelRepository;

/**
 * Class Data
 *
 * @package Bss\MultiWishlist\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_multiwishlist/general/enable';
    const XML_PATH_REMOVE_ITEM_ADDCART = 'bss_multiwishlist/general/remove_item_addcart';
    const XML_PATH_REDIRECT = 'bss_multiwishlist/general/redirect';

    /**
     * @var CustomerSessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlistFactory;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $http;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var mixed
     */
    protected $customerSession = null;

    /**
     * @var WishlistLabelRepository
     */
    protected $wishlistLabelRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param CustomerSessionFactory $customerSession
     * @param CollectionFactory $collectionFactory
     * @param WishlistFactory $coreWishlistFactory
     * @param HttpContext $httpContext
     * @param ProductMetadataInterface $productMetaData
     * @param \Magento\Framework\App\Request\Http $http
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param WishlistLabelRepository $wishlistLabelRepository
     */
    public function __construct(
        Context $context,
        CustomerSessionFactory $customerSession,
        CollectionFactory $collectionFactory,
        WishlistFactory $coreWishlistFactory,
        HttpContext $httpContext,
        ProductMetadataInterface $productMetaData,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        WishlistLabelRepository $wishlistLabelRepository
    ) {
        parent::__construct($context);
        $this->customerSessionFactory = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->coreWishlistFactory = $coreWishlistFactory;
        $this->httpContext = $httpContext;
        $this->productMetaData = $productMetaData;
        $this->http = $http;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->wishlistLabelRepository = $wishlistLabelRepository;
    }

    /**
     * Return Http Request class
     *
     * @return \Magento\Framework\App\Request\Http
     */
    public function returnHttpRequest()
    {
        return $this->http;
    }

    /**
     * Return DataObject class
     *
     * @return \Magento\Framework\DataObjectFactory
     */
    public function returnDataObj()
    {
        return $this->dataObjectFactory;
    }

    /**
     * Is enable module
     *
     * @param null|int $storeId
     * @return string
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is enable Redirect
     *
     * @param null|int $storeId
     * @return string
     */
    public function isRedirect($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is enable return Item from add to cart
     *
     * @param null|int $storeId
     * @return string
     */
    public function removeItemAfterAddCart($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REMOVE_ITEM_ADDCART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * Get wishlist Labels
     *
     * @return mixed
     */
    public function getWishlistLabels()
    {
        $customer = $this->getCustomer();
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('customer_id', $customer->getId());
        return $collection;
    }

    /**
     * Get Label Ids
     *
     * @return array
     */
    public function getLabelIds()
    {
        $wishlist = $this->getWishlistLabels();
        $multiWishlist = [];
        $multiWishlist[0] = 0;
        foreach ($wishlist as $item) {
            if (!in_array($item->getId(), $multiWishlist)) {
                $multiWishlist[] = $item->getId();
            }
        }
        return $multiWishlist;
    }

    /**
     * Get wishlist Item collection
     *
     * @param int $id
     * @return mixed
     */
    public function getWishlistItemsCollection($id)
    {
        $customer = $this->getCustomer();
        if ($customer->getId()) {
            $wishlist = $this->getWishlist($customer->getId());
            return $wishlist->getItemCollection()->addFieldToFilter('multi_wishlist_id', $id);
        }
        return 0;
    }

    /**
     * Get wishlist Item collection Shared
     *
     * @param int $multiWishlistId
     * @param int $customerId
     * @return mixed
     */
    public function getWishlistItemCollectionShared($multiWishlistId, $customerId)
    {
        if ($customerId) {
            $wishlist = $this->getWishlist($customerId);
            return $wishlist->getItemCollection()->addFieldToFilter('multi_wishlist_id', $multiWishlistId);
        }
        return 0;
    }

    /**
     * Get wishlist Collection
     *
     * @return mixed
     */
    public function getWishlistCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Get wishlist name from id
     *
     * @param int $id
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWishlistName($id)
    {
        if ($id == 0) {
            return __('Main');
        }
        return $this->wishlistLabelRepository->getById($id)->getWishlistName();
    }

    /**
     * Get Param
     *
     * @param string $param
     * @return string
     */
    public function getParamUrl($param)
    {
        return $this->_request->getParam($param);
    }

    /**
     * Get customer
     *
     * @return mixed
     */
    public function getCustomer()
    {
        if (!$this->customerSession) {
            $this->customerSession = $this->customerSessionFactory->create()->getCustomer();
        }
        return $this->customerSession;
    }

    /**
     * Get wishlist from customer
     *
     * @param int $customerId
     * @return mixed
     */
    public function getWishlist($customerId)
    {
        return $this->coreWishlistFactory->create()->loadByCustomerId($customerId, true);
    }
}
