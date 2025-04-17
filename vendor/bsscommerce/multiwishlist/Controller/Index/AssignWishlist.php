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
namespace Bss\MultiWishlist\Controller\Index;

use Bss\MultiWishlist\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class AssignWishlist
 *
 * @package Bss\MultiWishlist\Controller\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignWishlist extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @var $buyRequest
     */
    protected $buyRequest;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $orderItems;

    /**
     * @var array
     */
    private $groupProduct = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * AssignWishlist constructor.
     * @param Action\Context $context
     * @param Helper $helper
     * @param JsonFactory $resultJsonFactory
     * @param WishlistFactory $coreWishlist
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        Helper $helper,
        JsonFactory $resultJsonFactory,
        WishlistFactory $coreWishlist,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        Validator $formKeyValidator
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreWishlist = $coreWishlist;
        $this->customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Assign item to wishlist group
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|mixed
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['type']) && $params['type'] == 'addmultiple') {
            return $this->addMultiple($params);
        } else {
            return $this->addSimple($params);
        }
    }

    /**
     * Add simple product
     *
     * @param array $params
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|mixed
     */
    protected function addSimple($params)
    {
        $var = $wishlistIds = [];
        $wishlistIds = $this->returnWishlistIds($params);
        $productId = $this->returnProductId($params);
        $customerData = $this->customerSession->getCustomer();

        if ($this->checkError($productId, $wishlistIds)) {
            $var["result"] = "error";
            $var["message"] = '<div class="message-error error message"><div data-bind=\'html: message.text\'>' .
                __('Please try again.') . '</div></div>';
            $jsonResult = $this->resultJsonFactory->create();
            $jsonResult->setData($var);
            return $jsonResult;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $session = $this->customerSession;

        $wishlistName = [];
        $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
        try {
            foreach ($wishlistIds as $wishlistId) {
                try {
                    $product = $this->productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    $product = null;
                }
                if ($this->checkProductNotIsVisible($product)) {
                    $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
                    $resultRedirect->setPath('*/');
                    return $resultRedirect;
                }
                $params['wishlist_id'] = $wishlistId;
                $this->getRequest()->setPostValue($params);
                //check if $this->>buyRequest is empty => set value for buyRequest
                $this->returnBuyRequest($params);

                $result = $wishlist->addNewItem($product, $this->buyRequest, false);
                $this->saveWishlist($result, $wishlist);
                $wishlistName[] = $this->helper->getWishlistName($wishlistId);
                $this->_eventManager->dispatch(
                    'wishlist_add_product',
                    ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                );
            }
            $message = __("%1 has been added to wish list %2.", $product->getName(), implode(',', $wishlistName));
            $this->messageManager->addSuccessMessage($message);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }
        if ($session->getBeforeWishlistRequest()) {
            $session->unsBeforeWishlistRequest();
            $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        }
        $var = $this->getUrlWishlist($var);
        return $this->setData($var, $resultRedirect, $wishlist, $params);
    }

    /**
     * Add multi product to wishlist
     *
     * @param array $params
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|mixed
     */
    protected function addMultiple($params)
    {
        $var = $wishlistIds = [];
        $wishlistIds = $this->returnWishlistIds($params);
        $items = $this->returnProducts($params);
        $customerData = $this->customerSession->getCustomer();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $session = $this->customerSession;
        $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
        try {
            $productName = $wishlistName = [];
            foreach ($items as $item) {
                $product = $item->getProduct();
                if ($this->checkError($product->getId(), $wishlistIds)) {
                    $var["result"] = "error";
                    $var["message"] = '<div class="message-error error message"><div data-bind=\'html: message.text\'>' .
                        __('Please try again.') . '</div></div>';
                    $jsonResult = $this->resultJsonFactory->create();
                    $jsonResult->setData($var);
                    return $jsonResult;
                }

                if ($this->validateDataType($item, $product)) {
                    continue;
                }

                foreach ($wishlistIds as $wishlistId) {
                    $data = $item->getBuyRequest()->toArray();
                    $data['wishlist_id'] = $wishlistId;
                    $this->getRequest()->setPostValue($data);
                    $this->buyRequest = [];
                    //check if $this->>buyRequest is empty => set value for buyRequest
                    $this->returnBuyRequest($data);

                    $result = $wishlist->addNewItem($product, $this->buyRequest, false);
                    $this->saveWishlist($result, $wishlist);
                    $wishlistName[$wishlistId] = $this->helper->getWishlistName($wishlistId);
                    $this->_eventManager->dispatch(
                        'wishlist_add_product',
                        ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                    );
                }
                $productName[] = $product->getName();
            }
            foreach ($this->groupProduct as $gProduct) {
                $product = $gProduct['product'];
                foreach ($wishlistIds as $wishlistId) {
                    $data['super_group'] = $gProduct['super_group'];
                    $data['wishlist_id'] = $wishlistId;
                    $this->getRequest()->setPostValue($data);
                    $this->buyRequest = [];
                    //check if $this->>buyRequest is empty => set value for buyRequest
                    $this->returnBuyRequest($data);
                    $result = $wishlist->addNewItem($product, $this->buyRequest, false);
                    $this->saveWishlist($result, $wishlist);
                    $wishlistName[] = $this->helper->getWishlistName($wishlistId);
                    $this->_eventManager->dispatch(
                        'wishlist_add_product',
                        ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                    );
                }
                $productName[] = $product->getName();
            }
            $this->messageManager->addSuccessMessage(
                __(
                    "%1 has been added to wish list %2.",
                    implode(',', $productName),
                    implode(',', $wishlistName)
                )
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the items to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the items to Wish List right now.')
            );
        }
        $this->getBeforeWishlist($session, $resultRedirect, $wishlist);
        $var = $this->getUrlWishlist($var);
        return $this->setData($var, $resultRedirect, $wishlist, $params);
    }

    /**
     * Get Before Wishlist Session
     *
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @return mixed
     */
    protected function getBeforeWishlist($session, $resultRedirect, $wishlist)
    {
        if ($session->getBeforeWishlistRequest()) {
            $session->unsBeforeWishlistRequest();
            $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        }
        return $this;
    }

    /**
     * Validate data on foreach function
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function validateDataType($item, $product)
    {
        if ($item->getProductType() === 'grouped') {
            $this->getGroupProduct($item);
            return true;
        }
        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify product id %1.'), $product->getId());
            return true;
        }
        return false;
    }

    /**
     * Has error
     *
     * @param int $productId
     * @param array $wishlistIds
     * @return bool
     */
    protected function checkError($productId, $wishlistIds)
    {
        if (!$productId || empty($wishlistIds)) {
            return true;
        }
        return false;
    }

    /**
     * Product is not visiable
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    protected function checkProductNotIsVisible($product)
    {
        if (!$product || !$product->isVisibleInCatalog()) {
            return true;
        }
        return false;
    }

    /**
     * Get buy request params
     *
     * @param array $params
     */
    protected function returnBuyRequest($params)
    {
        if (empty($this->buyRequest)) {
            $this->buyRequest = $this->helper->returnDataObj()->create()->addData($params);
        }
    }

    /**
     * Check isset wishlist id in params
     *
     * @param array $params
     * @return array
     */
    protected function returnWishlistIds($params)
    {
        return isset($params['wishlist_id']) ? $params['wishlist_id'] : [0];
    }

    /**
     * Get product Id
     *
     * @param array $params
     * @return int|null
     */
    protected function returnProductId($params)
    {
        return isset($params['product']) ? (int)$params['product'] : null;
    }

    /**
     * Get products from Params
     *
     * @param array $params
     * @return array
     */
    protected function returnProducts($params)
    {
        if (isset($params['product'])) {
            $products = explode('__', $params['product']);
            foreach ($products as $product) {
                $productData = explode('_', $product);
                if (count($productData) < 2) {
                    continue;
                }
                $this->params[$productData[0]] = $productData[1];
            }
            if (!$this->orderItems && !empty($this->params)) {
                $this->orderItems = $this->orderItemCollectionFactory->create()->addFieldToSelect(
                    '*'
                )->addFieldToFilter(
                    'item_id',
                    ['in' => array_keys($this->params)]
                )->setOrder(
                    'created_at',
                    'desc'
                )->getItems();
            }
            return $this->orderItems;
        }

        return [];
    }

    /**
     * Save Wishlist
     *
     * @param mixed $result
     * @param mixed $wishlist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveWishlist($result, $wishlist)
    {
        if (is_string($result)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }
        $wishlist->save();
    }

    /**
     * Set data from Redirect Request
     *
     * @param array $var
     * @param mixed $resultRedirect
     * @param mixed $wishlist
     * @param array $params
     * @return mixed
     */
    protected function setData($var, $resultRedirect, $wishlist, $params)
    {
        if ($this->helper->returnHttpRequest()->isAjax()) {
            return $this->resultJsonFactory->create()->setData($var);
        } else {
            if (isset($params['bss_current_url']) && !$this->helper->isRedirect()) {
                return $resultRedirect->setPath($params['bss_current_url']);
            }
            $resultRedirect->setPath('wishlist', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        }
    }

    /**
     * Get wishlist url
     *
     * @param array $var
     * @return mixed
     */
    protected function getUrlWishlist($var)
    {
        if ($this->helper->isRedirect()) {
            $var["url"] = $this->_url->getUrl("wishlist");
        }
        return $var;
    }

    /**
     * Get group product from item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    private function getGroupProduct($item)
    {
        $productConfig = $item->getProductOptionByCode('super_product_config');
        if (isset($productConfig['product_id'])) {
            try {
                $superGroup = $item->getProduct()->getId();
                $product = $this->productRepository->getById($productConfig['product_id']);
                if (isset($this->groupProduct[$productConfig['product_id']])) {
                    $productId = $productConfig['product_id'];
                    $this->groupProduct[$productId]['super_group'][$superGroup] = $this->params[$item->getId()];
                } else {
                    $this->groupProduct[$productConfig['product_id']] = [
                        'super_group' => [$superGroup => $this->params[$item->getId()]],
                        'product' => $product
                    ];
                }
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
    }
}
