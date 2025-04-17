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
use Bss\MultiWishlist\Model\WishlistLabel;
use Bss\MultiWishlist\Model\WishlistLabelRepository;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Delete
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Delete extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Delete constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param CustomerSession $customerSession
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        CustomerSession $customerSession,
        Helper $helper,
        WishlistLabel $wishlistLabel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
    }

    /**
     * Delete wishlist execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $mWishlistId = $this->getRequest()->getParam('mWishlistId');
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$isLoggedIn) {
            $this->messageManager->addErrorMessage(__('You must logged in to perform this action.'));
            $resultRedirect->setPath('customer/account/login/');
            return $resultRedirect;
        }
        if (!$mWishlistId) {
            $this->messageManager->addErrorMessage(__('An error occurred, please try again later.'));
            $resultRedirect->setPath('wishlist');
            return $resultRedirect;
        }
        $model = $this->wishlistLabel;
        $wishList = $model->load($mWishlistId);
        $customerId = $this->customerSession->getCustomerId();
        $wishListCustomerId = $wishList->getData('customer_id');
        if ((int)$wishListCustomerId !== (int)$customerId) {
            $this->messageManager->addErrorMessage(__("You can't delete a wishlist that is not belong to you!"));
            $resultRedirect->setPath('wishlist');
            return $resultRedirect;
        }
        $items = $this->helper->getWishlistItemsCollection($mWishlistId);
        $coreWishlistId = $items->setPageSize(1, 1)->getLastItem()->getWishlistId();
        $coreWishlist = $this->wishlistProvider->getWishlist($coreWishlistId);
        try {
            $model->load($mWishlistId)->delete();
            $model->getResource()->deleteItems($items, $mWishlistId);
            $coreWishlist->save();
            $this->messageManager->addSuccessMessage(__('The Wishlist has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        $resultRedirect->setPath('wishlist', ['wishlist_id' => $coreWishlistId]);
        return $resultRedirect;
    }
}
