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

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Allcart
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Allcart extends \Magento\Wishlist\Controller\Index\Allcart
{
    /**
     * @param \Bss\MultiWishlist\Helper\Data $helperData
     */
    protected $helperData;

    /**
     * Allcart constructor.
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     * @param \Bss\MultiWishlist\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        WishlistProviderInterface $wishlistProvider,
        Validator $formKeyValidator,
        ItemCarrier $itemCarrier,
        \Bss\MultiWishlist\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $wishlistProvider, $formKeyValidator, $itemCarrier);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $mwlId = $this->getRequest()->getParam('mwlId');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->helperData->isEnable()) {
            $redirectUrl = $this->itemCarrier->
            moveAllToCartExtend($wishlist, $this->getRequest()->getParam('qty'), $mwlId);
        } else {
            $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        }
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
