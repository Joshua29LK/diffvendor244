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
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

/**
 * Class AssignWishlistFromCart
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class AssignWishlistFromCart extends \Magento\Wishlist\Controller\Index\Fromcart
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * AssignWishlistFromCart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param WishlistHelper $wishlistHelper
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        WishlistHelper $wishlistHelper,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        Validator $formKeyValidator,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $wishlistProvider,
            $wishlistHelper,
            $cart,
            $cartHelper,
            $escaper,
            $formKeyValidator
        );
    }

    /**
     * Move product to wishlist From Cart execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if ($this->helper->isEnable()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            try {
                $params = $this->getRequest()->getParams();
                $customerData = $this->helper->getCustomer();
                $itemId = isset($params['item']) ? (int)$params['item'] : null;
                $wishlistId = isset($params['wishlist_id']) ? (int)$params['wishlist_id'][0] : null;

                $item = $this->cart->getQuote()->getItemById($itemId);
                if (!$item) {
                    $this->messageManager->addErrorMessage(__('The requested cart item doesn\'t exist.'));
                } else {
                    $wishlist = $this->helper->getWishlist($customerData->getId());
                    $productId = $item->getProductId();
                    $buyRequest = $item->getBuyRequest();
                    $params['wishlist_id'] = $wishlistId;
                    $this->getRequest()->setPostValue($params);
                    $wishlist->addNewItem($productId, $buyRequest, false);
                    $this->cart->getQuote()->removeItem($itemId);
                    $this->cart->save();
                    $wishlist->save();
                    $this->messageManager->addSuccessMessage(
                        __("%1 has been moved to your wish list.", $this->escaper->escapeHtml($item->getProduct()->getName()))
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Please try again.'));
            }
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        } else {
            return parent::execute();
        }
    }
}
