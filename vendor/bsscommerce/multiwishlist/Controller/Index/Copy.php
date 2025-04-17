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
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class Copy
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Copy extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    protected $optionFactory;

    /**
     * Copy constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Helper $helper
     * @param WishlistItem $wishlistItem
     * @param WishlistFactory $coreWishlist
     * @param \Magento\Customer\Model\Session $customerSession
     * @param WishlistItem\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Helper $helper,
        WishlistItem $wishlistItem,
        WishlistFactory $coreWishlist,
        \Magento\Customer\Model\Session $customerSession,
        WishlistItem\OptionFactory $optionFactory
    ) {
        $this->helper = $helper;
        $this->wishlistItem = $wishlistItem;
        $this->coreWishlist = $coreWishlist;
        $this->customerSession = $customerSession;
        $this->optionFactory = $optionFactory;
        parent::__construct($context);
    }

    /**
     * Assign item to wishlist group.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $params = $this->getRequest()->getParams();

        $wishlistIds = isset($params['wishlist_id']) ? $params['wishlist_id'] : null;
        $itemId = isset($params['item']) ? (int)$params['item'] : null;
        $customerData = $this->customerSession->getCustomer();
        $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);
        if (!empty($wishlistIds) && $itemId) {
            try {
                foreach ($wishlistIds as $wishlistId) {
                    $params['wishlist_id'] = $wishlistId;
                    $this->getRequest()->setPostValue($params);
                    $product = $this->saveWishlist($itemId, $wishlist);
                    $wishlistName[] = $this->helper->getWishlistName($wishlistId);
                }

                $this->messageManager->addSuccessMessage(__("%1 has been copied to wish list %2.", $product->getName(), implode(',', $wishlistName)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Please try again.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    /**
     * Save Wishlist
     *
     * @param int $itemId
     * @param mixed $wishlist
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveWishlist($itemId, $wishlist)
    {
        $wlItem = $this->wishlistItem->load($itemId);
        $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
        $wlItem->setOptions($options->getOptionsByItem($itemId));
        $buyRequest = $wlItem->getBuyRequest();
        if ($wlItem->getWishlistItemId()) {
            $product = $wlItem->getProduct();
            $items = $wishlist->addNewItem($product, $buyRequest, false);
            $items->setData('description', $wlItem->getData('description'));
            $wishlist->save();
        }
        return $product;
    }
}
