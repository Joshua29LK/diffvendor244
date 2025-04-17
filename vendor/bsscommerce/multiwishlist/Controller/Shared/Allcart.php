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
namespace Bss\MultiWishlist\Controller\Shared;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\Shared\WishlistProvider;
use Magento\Wishlist\Model\ItemCarrier;
use Bss\MultiWishlist\Helper\Data;
use Bss\MultiWishlist\Model\ItemCarrier as ItemCarrierModel;

/**
 * Class Allcart
 *
 * @package Bss\MultiWishlist\Controller\Shared
 */
class Allcart extends \Magento\Wishlist\Controller\Shared\Allcart
{
    /**
     * @var \Bss\MultiWishlist\Model\ItemCarrierModel
     */
    protected $itemCarrierModel;

    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param WishlistProvider $wishlistProvider
     * @param ItemCarrier $itemCarrier
     * @param Data $dataHelper
     * @param ItemCarrierModel $itemCarrierModel
     */
    public function __construct(
        Context $context,
        WishlistProvider $wishlistProvider,
        ItemCarrier $itemCarrier,
        Data $dataHelper,
        ItemCarrierModel $itemCarrierModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->itemCarrierModel = $itemCarrierModel;
        parent::__construct($context, $wishlistProvider, $itemCarrier);
    }

    /**
     * Rewrite AllCart function
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if ($this->dataHelper->isEnable()) {
            $multiWishlistId = $this->getRequest()->getParam('mwishlist_id');
            $wishlist = $this->wishlistProvider->getWishlist();
            if (!$wishlist) {
                /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
                $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                $resultForward->forward('noroute');
                return $resultForward;
            }
            $redirectUrl = $this->itemCarrierModel->
            moveAllToCartExtend($wishlist, $this->getRequest()->getParam('qty'), $multiWishlistId);
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        } else {
            parent::execute();
        }
    }
}
