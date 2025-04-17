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

use Magento\Framework\App\Action\Action;
use Bss\MultiWishlist\Helper\Data as Helper;
use Bss\MultiWishlist\Model\WishlistLabel;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Edit
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Edit extends Action
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
     * @var Helper
     */
    protected $helper;

    /**
     * @var WishlistLabel
     */
    protected $wishlistLabel;

    /**
     * @var WishlistItem
     */
    protected $wishlistItem;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Edit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param Helper $helper
     * @param WishlistLabel $wishlistLabel
     * @param WishlistItem $wishlistItem
     * @param Validator $formKeyValidator
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $redirectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        Helper $helper,
        WishlistLabel $wishlistLabel,
        WishlistItem $wishlistItem,
        Validator $formKeyValidator,
        JsonFactory $resultJsonFactory,
        RedirectFactory $redirectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->wishlistProvider = $wishlistProvider;
        $this->helper = $helper;
        $this->wishlistLabel = $wishlistLabel;
        $this->wishlistItem = $wishlistItem;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Edit wishlist execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $mWishlistId = $this->getRequest()->getParam('mWishlistId');
        $mWishlistName = $this->getRequest()->getParam('mWishlistName');
        $jsonResult = $this->resultJsonFactory->create();
        if (!$mWishlistId) {
            $this->messageManager->addErrorMessage(__('An error occurred, please try again later.'));
            $redirectResult = $this->redirectFactory->create();
            $redirectResult->setPath('wishlist');

            return $redirectResult;
        }
        $model = $this->wishlistLabel;
        $maxLength = 255;
        if (strlen($mWishlistName) > $maxLength) {
            $model->load($mWishlistId);
            $var["result"] = "error";
            $var["rollbackName"] = $model->getDataByKey('wishlist_name');
            $this->messageManager->addErrorMessage(__('Wishlist name must less than %1 character.', $maxLength));
            $jsonResult->setData($var);

            return $jsonResult;
        }
        try {
            $model->load($mWishlistId);
            $model->setWishlistName($mWishlistName);
            $model->save();
            $var["result"] = "success";
            $var["mWishlistName"] = $mWishlistName;
            $this->messageManager->addSuccessMessage(__('The wishlist has renamed successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Please try again.'));
        }
        $jsonResult->setData($var);
        return $jsonResult;
    }
}
