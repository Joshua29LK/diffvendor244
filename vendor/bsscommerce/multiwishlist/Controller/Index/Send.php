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

use Magento\Framework\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\Generic as WishlistSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Send
 *
 * @package Bss\MultiWishlist\Controller\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Send extends \Magento\Wishlist\Controller\Index\Send
{
    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Send constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Helper\View $customerHelperView
     * @param WishlistSession $wishlistSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Bss\MultiWishlist\Helper\Data $helper
     * @param \Magento\Framework\Escaper $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Helper\View $customerHelperView,
        WishlistSession $wishlistSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Bss\MultiWishlist\Helper\Data $helper,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct(
            $context,
            $formKeyValidator,
            $customerSession,
            $wishlistProvider,
            $wishlistConfig,
            $transportBuilder,
            $inlineTranslation,
            $customerHelperView,
            $wishlistSession,
            $scopeConfig,
            $storeManager
        );
        $this->helper = $helper;
        $this->escaper = $escaper;
    }

    /**
     * Send Email execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     * @throws \Zend_Validate_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $wishlist = $this->wishlistProvider->getWishlist();
        $this->exceptionWishlist($wishlist);

        $sharingLimit = $this->_wishlistConfig->getSharingEmailLimit();
        $textLimit = $this->_wishlistConfig->getSharingTextLimit();
        $emailsLeft = $sharingLimit - $wishlist->getShared();

        $emails = $this->getRequest()->getPost('emails');
        $emails = empty($emails) ? $emails : explode(',', $emails);

        $error = false;
        $message = (string)$this->getRequest()->getPost('message');
        if (strlen($message) > $textLimit) {
            $error = __('Message length must not exceed %1 symbols', $textLimit);
        } else {
            $message = nl2br($this->escaper->escapeHtml($message));
            if (empty($emails)) {
                $error = __('Please enter an email address.');
            } else {
                if (count($emails) > $emailsLeft) {
                    $error = __('This wish list can be shared %1 more times.', $emailsLeft);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = trim($email);
                        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                            $error = __('Please enter a valid email address.');
                            break;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
            $this->wishlistSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->addLayoutHandles($resultLayout);
        $this->inlineTranslation->suspend();

        $sent = 0;

        try {
            $customer = $this->_customerSession->getCustomerDataObject();
            $customerName = $this->_customerHelperView->getCustomerName($customer);

            $message .= $this->getRssLink($wishlist->getId(), $resultLayout);
            $emails = array_unique($emails);
            $sharingCode = $wishlist->getSharingCode();
            $multiWishlistId = $this->getRequest()->getParam('mwishlist_id');
            $wishListName = $this->helper->getWishlistName($multiWishlistId);
            try {
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getStoreId(),
                        ]
                    )->setTemplateVars(
                        [
                            'customer' => $customer,
                            'customerName' => $customerName,
                            'salable' => $wishlist->isSalable() ? 'yes' : '',
                            'items' => $this->getWishlistItems($resultLayout),
                            'viewOnSiteLink' => $this->_url->
                            getUrl('*/shared/index', ['code' => $sharingCode, 'mwishlist_id' => $multiWishlistId]),
                            'message' => $message,
                            'store' => $this->storeManager->getStore(),
                            'wishlistName' => $wishListName,
                        ]
                    )->setFrom(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $email
                    )->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (\Exception $e) {
                $wishlist->setShared($wishlist->getShared() + $sent);
                $wishlist->save();
                throw $e;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();

            $this->inlineTranslation->resume();

            $this->_eventManager->dispatch('wishlist_share', ['wishlist' => $wishlist]);
            $this->messageManager->addSuccessMessage(__('Your wish list has been shared.'));
            $resultRedirect->setPath('*/*', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->wishlistSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
    }

    /**
     * Throw exception when send email
     *
     * @param mixed $wishlist
     * @throws NotFoundException
     */
    protected function exceptionWishlist($wishlist)
    {
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
