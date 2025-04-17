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
namespace Bss\MultiWishlist\Model;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Wishlist;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ItemCarrier extends \Magento\Wishlist\Model\ItemCarrier
{

    /**
     * Move all to cart
     *
     * @param Wishlist $wishlist
     * @param array $qtys
     * @param int $mwlId
     * @return string
     * @throws LocalizedException
     */
    public function moveAllToCartExtend(Wishlist $wishlist, $qtys, $mwlId = 0)
    {
        $isOwner = $wishlist->isOwner($this->customerSession->getCustomerId());
        $messages = [];
        $addedProductsName = [];
        $notSalable = [];

        $cart = $this->cart;
        $collection = $wishlist->getItemCollection()->setVisibilityFilter()->
            addFieldToFilter('multi_wishlist_id', $mwlId);

        foreach ($collection as $item) {
            /** @var $item \Magento\Wishlist\Model\Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                $item = $this->setQtyItem($qtys, $item);
                $item->getProduct()->setDisableAddToCart($disableAddToCart);

                // Add to cart
                if ($item->addToCart($cart, $isOwner)) {
                    $addedProductsName[] = '"'.$item->getProduct()->getName().'"';
                }
            } catch (LocalizedException $e) {
                if ($e instanceof ProductException) {
                    $notSalable[] = '"' . $item->getProduct()->getName() . '"';
                } else {
                    $messages[] = __('%1 for "%2".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }
                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                $this->deleteCartItem($cartItem);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $messages[] = __('We can\'t add this item to your shopping cart right now.');
            }
        }

        $indexUrl = $this->getIndexUrl($isOwner, $wishlist, $mwlId);
        $redirectUrl = $this->getRedirectUrl($indexUrl);
        $redirectUrl = $this->addMessageError($notSalable, $messages, $redirectUrl, $indexUrl);
        if ($addedProductsName) {
            // save wishlist model for setting date of last update
            try {
                $wishlist->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t update the Wish List right now.'));
                $redirectUrl = $indexUrl;
            }

            $this->messageManager->addSuccessMessage(
                __(
                    '%1 product(s) have been added to shopping cart: %2.',
                    count($addedProductsName),
                    join(', ', $addedProductsName)
                )
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }
        $this->helper->calculate();
        return $redirectUrl;
    }

    /**
     * Get index url
     *
     * @param bool $isOwner
     * @param mixed $wishlist
     * @param int $mwlId
     * @return string
     */
    protected function getIndexUrl($isOwner, $wishlist, $mwlId)
    {
        if ($isOwner) {
            return $this->helper->getListUrl($wishlist->getId());
        } else {
            $param = ['code' => $wishlist->getSharingCode()];
            if ($mwlId) {
                $param['mwishlist_id'] = $mwlId;
            }
            return $this->urlBuilder->getUrl('wishlist/shared', $param);
        }
    }

    /**
     * Get reidrect url
     *
     * @param string $indexUrl
     * @return string
     */
    protected function getRedirectUrl($indexUrl)
    {
        if ($this->cartHelper->getShouldRedirectToCart()) {
            return $this->cartHelper->getCartUrl();
        } elseif ($this->redirector->getRefererUrl()) {
            return $this->redirector->getRefererUrl();
        } else {
            return $indexUrl;
        }
    }

    /**
     * Add error message
     *
     * @param mixed $notSalable
     * @param array $messages
     * @param string $redirectUrl
     * @param string $indexUrl
     * @return string
     */
    protected function addMessageError($notSalable, $messages, $redirectUrl, $indexUrl)
    {
        if ($notSalable) {
            $messages[] = __(
                'We couldn\'t add the following product(s) to the shopping cart: %1.',
                join(', ', $notSalable)
            );
        }
        if ($messages) {
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
            $redirectUrl = $indexUrl;
        }
        return $redirectUrl;
    }

    /**
     * Delete Cart item
     *
     * @param mixed $cartItem
     */
    protected function deleteCartItem($cartItem)
    {
        if ($cartItem) {
            $this->cart->getQuote()->deleteItem($cartItem);
        }
    }

    /**
     * Add product
     *
     * @param mixed $item
     * @param mixed $cart
     * @param bool $isOwner
     * @param array $addedProducts
     * @return array
     */
    protected function addProduct($item, $cart, $isOwner, $addedProducts)
    {
        if ($item->addToCart($cart, $isOwner)) {
            $addedProducts[] = $item->getProduct();
        }
        return $addedProducts;
    }

    /**
     * Set qty to item
     *
     * @param array $qtys
     * @param mixed $item
     * @return mixed
     */
    protected function setQtyItem($qtys, $item)
    {
        if (isset($qtys[$item->getId()])) {
            $qty = $this->quantityProcessor->process($qtys[$item->getId()]);
            if ($qty) {
                $item->setQty($qty);
            }
        }
        return $item;
    }
}
