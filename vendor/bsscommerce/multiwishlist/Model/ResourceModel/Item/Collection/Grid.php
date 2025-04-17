<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Model\ResourceModel\Item\Collection;

/**
 * Class Grid
 *
 * @package Bss\MultiWishlist\Model\ResourceModel\Item\Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends \Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $data;

    /**
     * Grid constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Sales\Helper\Admin $adminhtmlSales
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\App\ResourceConnection $coreResource
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory
     * @param \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory
     * @param \Magento\Wishlist\Model\ResourceModel\Item $resource
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\MultiWishlist\Helper\Data $data
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Sales\Helper\Admin $adminhtmlSales,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Magento\Wishlist\Model\ResourceModel\Item $resource,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Registry $registry,
        \Bss\MultiWishlist\Helper\Data $data,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->data = $data;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $stockConfiguration,
            $adminhtmlSales,
            $storeManager,
            $date,
            $wishlistConfig,
            $productVisibility,
            $coreResource,
            $optionCollectionFactory,
            $productCollectionFactory,
            $catalogConfFactory,
            $catalogAttrFactory,
            $resource,
            $appState,
            $registry,
            $connection
        );
    }

    /**
     * Join multi_wishlist_id table to select query
     *
     * @return \Bss\MultiWishlist\Model\ResourceModel\Item\Collection\Grid $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if ($this->data->isEnable()) {
            $this->getSelect()->joinLeft(
                ['ct' => $this->getTable('bss_multiwishlist')],
                'main_table.multi_wishlist_id = ct.multi_wishlist_id',
                '*'
            );
        }
        return $this;
    }
}
