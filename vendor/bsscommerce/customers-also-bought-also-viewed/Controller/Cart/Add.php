<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Add
 * @package Bss\CABAV\Controller\Cart
 */
class Add extends Action
{
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Add constructor.
     * @param Context $context
     * @param FormKey $formKey
     * @param Cart $cart
     * @param Product $product
     * @param ScopeConfigInterface $scopeConfig
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        Cart $cart,
        Product $product,
        ScopeConfigInterface $scopeConfig,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $params = $this->getRequest()->getParams();
        $redirectUrl = false;
        if (isset($params['current_url'])) {
            $redirectUrl = $params['current_url'];
        }
        if (!isset($params['product_id'])) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add this item to your shopping cart right now.')
            );
        } else {
            $productId = $params['product_id'];
            $params = [
                'form_key' => $params['form_key'],
                'product' => $productId,
                'qty' => 1
            ];
            $product = $this->product->load($productId);
            if ($product->getId()) {
                try {
                    $this->cart->addProduct($product, $params);
                    $this->cart->save();
                    if ($this->shouldRedirectToCart()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                        $resultRedirect->setUrl($this->getCartUrl());
                        return $resultRedirect;
                    } else {
                        $this->messageManager->addComplexSuccessMessage(
                            'addCartSuccessMessage',
                            [
                                'product_name' => $product->getName(),
                                'cart_url' => $this->getCartUrl(),
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('We can\'t add this item to your shopping cart right now.')
                );
            }
        }
        if ($redirectUrl) {
            $resultRedirect->setUrl($redirectUrl);
        } else {
            $resultRedirect->setUrl($this->_url->getBaseUrl());
        }
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    private function shouldRedirectToCart()
    {
        return $this->scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
