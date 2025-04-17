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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Create
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Create extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $validatorFormKey;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Create constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $validatorFormKey
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param CustomerSession $customerSession
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $validatorFormKey,
        \Magento\Framework\Serialize\Serializer\Json $json,
        CustomerSession $customerSession,
        Helper $helper,
        WishlistLabel $wishlistLabel,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->validatorFormKey = $validatorFormKey;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Create new wishlist execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setAfterAuthUrl($this->_url->getCurrentUrl());
            $this->customerSession->authenticate();
        }
        $post = $this->getRequest()->getPost();
        $name = $post['new_wlname'];
        $param = $this->getRequest()->getParams();
        $model = $this->wishlistLabel;
        $response = [];
        $layout = $this->_view->getLayout();
        $template = 'Bss_MultiWishlist::popup.phtml';
        $customer = $this->customerSession->getCustomer();
        $collection = $this->helper->getWishlistCollection();
        $collection = $collection->addFieldToFilter('wishlist_name', $name)
            ->addFieldToFilter('customer_id', $customer->getId());
        $result = $collection->setPageSize(1, 1)->getLastItem();

        $validation = $this->validate($result, $name);
        if (!isset($param['ajax']) && $validation) {
            $this->messageManager->addErrorMessage($validation);

            return $resultRedirect->setPath('wishlist');
        }

        if (isset($param['ajax']) && $validation) {
            $message = $validation;
            $response['error'] = "<div class='message-error error message'>
                    <div data-bind='html: message.text'>" . $message . "</div></div>";
            $jsonResult = $this->resultJsonFactory->create();
            $jsonResult->setData($response);

            return $jsonResult;
        }

        try {
            $model->setCustomerId($customer->getId());
            $model->setWishlistName($name);
            $model->save();

            if (!isset($param['ajax'])) {
                $this->messageManager->addSuccessMessage(__('Successfully saved the wishlist.'));
                $resultRedirect->setPath('wishlist');

                return $resultRedirect;
            }

            $response['success'] = "<div class='message-success success message'>
                        <div data-bind='html: message.text'>" . __('Successfully saved the wishlist.') . "</div></div>";
            $response['html'] = $layout->createBlock(\Bss\MultiWishlist\Block\Popup::class)
                ->setTemplate($template)
                ->setAction(false)
                ->toHtml();
            $jsonResult = $this->resultJsonFactory->create();
            $jsonResult->setData($response);

            return $jsonResult;
        } catch (\Exception $e) {
            return $resultRedirect->setPath('wishlist');
        }
    }

    /**
     * Validate request param
     *
     * @param object $result
     * @param string $name
     * @return string|bool
     */
    public function validate($result, $name)
    {
        if ($this->validateLength($name)) {
            return $this->validateLength($name);
        }

        if ($this->validateExistName($result, $name)) {
            return $this->validateExistName($result, $name);
        }

        return false;
    }

    /**
     * Check if name exist
     *
     * @param object $result
     * @param string $name
     * @return string|bool
     */
    public function validateExistName($result, $name)
    {
        if ($result->getId() || strtolower($name) == 'main') {
            return __('Already exist a Wishlist. Please choose a different name.');
        }

        return false;
    }

    /**
     * Validate length of name of mw
     *
     * @param string $name
     * @return string|bool
     */
    public function validateLength($name)
    {
        $maxLength = 255;

        if (strlen($name) > $maxLength) {
            return __('Wishlist name must less than %1 character.', $maxLength);
        }

        return false;
    }
}
