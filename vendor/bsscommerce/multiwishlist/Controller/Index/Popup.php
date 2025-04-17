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

use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Popup
 *
 * @package Bss\MultiWishlist\Controller\Index
 */
class Popup extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Popup constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $json,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Open popup execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $jsonData = $this->resultJsonFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setAfterAuthUrl($this->_url->getCurrentUrl());
            $result['url'] = $this->_url->getUrl('customer/account/login');
            $jsonData->setData($result);
            return $jsonData;
        }
        $params = $this->getRequest()->getParams();
        $action = isset($params['action']) ? $params['action'] : false;
        $unwishlist = isset($params['wishlist_id']) ? $params['wishlist_id'] : false;
        $template = 'Bss_MultiWishlist::popup.phtml';
        $data = [
            'data' => [
                'action' => $action, 'unwishlist' => $unwishlist
            ]
        ];
        $result = $this->_view->getLayout()
                              ->createBlock(\Bss\MultiWishlist\Block\Popup::class, '', $data)
                              ->setTemplate($template)->toHtml();
        $jsonData->setData($result);
        return $jsonData;
    }
}
