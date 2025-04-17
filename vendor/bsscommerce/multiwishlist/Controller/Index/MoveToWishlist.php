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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Class MoveToWishlist
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class MoveToWishlist extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistFactory
     */
    protected $coreWishlist;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var WishlistItem\OptionFactory
     */
    protected $optionFactory;

    /**
     * MoveToWishlist constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param CustomerSession $customerSession
     * @param Helper $helper
     * @param WishlistFactory $coreWishlist
     * @param WishlistItem $wishlistItem
     * @param WishlistItem\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        Helper $helper,
        WishlistFactory $coreWishlist,
        WishlistItem $wishlistItem,
        WishlistItem\OptionFactory $optionFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->wishlistItem = $wishlistItem;
        $this->coreWishlist = $coreWishlist;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Move to wishlist execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerData = $this->customerSession->getCustomer();
        $wishlist = $this->coreWishlist->create()->loadByCustomerId($customerData->getId(), true);

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        $resultRedirect->setUrl($redirectUrl);

        $params = $this->getRequest()->getParams();
        $itemId = isset($params['item']) ? (int)$params['item'] : null;
        $wishlistId = isset($params['wishlist_id']) ? (int)$params['wishlist_id'][0] : null;

        if ($itemId === null) {
            $this->messageManager->addErrorMessage(__('Please try again.'));
            return $resultRedirect;
        }
        try {
            $wlItem = $this->wishlistItem->load($itemId);
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $wlItem->setOptions($options->getOptionsByItem($itemId));
            $buyRequest = $wlItem->getBuyRequest();
            if ($wlItem->getWishlistItemId()) {
                $product = $wlItem->getProduct();
                $params['wishlist_id'] = $wishlistId;
                $this->getRequest()->setPostValue($params);
                $items = $wishlist->addNewItem($product, $buyRequest, false);
                $items->setData('description', $wlItem->getData('description'));
                $wishlist->save();
                $wlItem->delete();
            }
            $this->messageManager->addSuccessMessage(
                __(
                    "%1 has been moved to wish list %2.",
                    $wlItem->getProduct()->getName(),
                    $this->helper->getWishlistName($wishlistId)
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        return $resultRedirect;
    }
}
