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
namespace Bss\MultiWishlist\Model;

use Bss\MultiWishlist\Api\Data\MultiwishlistInterface;
use Bss\MultiWishlist\Api\Data\MultiwishlistInterfaceFactory;
use Bss\MultiWishlist\Api\MultiwishlistRepositoryInterface;
use Bss\Multiwishlist\Model\ResourceModel\WishlistLabel\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class WishlistLabelRepository
 *
 * @package Bss\MultiWishlist\Model
 */
class WishlistLabelRepository implements MultiwishlistRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    protected $wishlistCollection;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceModel\WishlistLabel
     */
    protected $resource;

    /**
     * @var MultiwishlistInterfaceFactory
     */
    protected $mWishlistIterfaceFactory;

    /**
     * @var WishlistLabelFactory
     */
    protected $wishlistLabelFactory;

    /**
     * WishlistLabelRepository constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param CollectionFactory $wishlistCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\WishlistLabel $resource
     * @param MultiwishlistInterfaceFactory $mWishlistIterfaceFactory
     * @param WishlistLabelFactory $wishlistLabelFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        \Bss\MultiWishlist\Model\ResourceModel\WishlistLabel\CollectionFactory $wishlistCollection,
        SearchResultsInterfaceFactory $searchResultsFactory,
        ResourceModel\WishlistLabel $resource,
        MultiwishlistInterfaceFactory $mWishlistIterfaceFactory,
        WishlistLabelFactory $wishlistLabelFactory
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->wishlistCollection = $wishlistCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->resource = $resource;
        $this->mWishlistIterfaceFactory = $mWishlistIterfaceFactory;
        $this->wishlistLabelFactory = $wishlistLabelFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById($wishlistId)
    {
        if (!isset($this->instances[$wishlistId])) {
            $mWishlist = $this->wishlistLabelFactory->create();
            $this->resource->load($mWishlist, $wishlistId);
            if (!$mWishlist->getId()) {
                throw new NoSuchEntityException(__('Wish list with id "%1" does not exist.', $wishlistId));
            }
            $this->instances[$wishlistId] = $mWishlist;
        }
        return $this->instances[$wishlistId];
    }

    /**
     * Get Wishlist Label
     *
     * @param int $wishlistId
     * @param int $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function get($wishlistId, $storeId = null)
    {
        $cacheKey = 'all';
        if ($storeId) {
            $cacheKey = $storeId;
        }
        if (!isset($this->instances[$wishlistId][$cacheKey])) {
            $mWishlist = $this->wishlistLabelFactory->create();

            $this->resource->load($mWishlist, $wishlistId);
            if (!$mWishlist->getId()) {
                throw NoSuchEntityException::singleField('id', $wishlistId);
            }
            $this->instances[$wishlistId][$cacheKey] = $mWishlist;
        }
        return $this->instances[$wishlistId][$cacheKey];
    }

    /**
     * Save bss multi wishlist
     *
     * @param \Bss\MultiWishlist\Api\Data\MultiwishlistInterface $multiwishlist
     * @return \Bss\MultiWishlist\Api\Data\MultiwishlistInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save($multiwishlist)
    {
        try {
            $validateExistName = $this->validateExistName($multiwishlist->getWishlistName());
            if (!$validateExistName) {
                $this->resource->save($multiwishlist);
                return $multiwishlist;
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save wishlist: %1',
                    $exception->getMessage()
                )
            );
        }
        throw new CouldNotSaveException(
            __($validateExistName)
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteByMultiWishlistId($multiWishlistId)
    {
        try {
            $wishlist = $this->getById($multiWishlistId);
            $this->resource->delete($wishlist);
            $result["status"] = [
                "success" => true,
                "message" => __("You deleted.")
            ];

        } catch (\Exception $exception) {
            $result["status"] = [
                "success" => false,
                "message" => __($exception->getMessage())
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function validateExistName($wishlistName)
    {
        if (!$wishlistName) {
            return __("wishlist name should be specified");
        }
        $searchCriteria = $this->criteriaBuilder->addFilter("wishlist_name", $wishlistName)
                               ->create();
        $wishlistCollection = $this->getList($searchCriteria);
        if ($wishlistCollection->getTotalCount() || strtolower($wishlistName) == 'main') {
            return __('Already exist a Wishlist. Please choose a different name.');
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->wishlistCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getListByCustomerId($customerId)
    {
        $searchCriteria = $this->criteriaBuilder->addFilter("customer_id", $customerId)
            ->create();
        return $this->getList($searchCriteria)->getItems();
    }
}
