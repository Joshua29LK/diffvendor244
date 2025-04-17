<?php
namespace Aheadworks\Followupemail2\Controller\Cart;

use Aheadworks\Followupemail2\Model\Event\Queue\CartRestorer;
use Magento\Framework\App\Action\Context;

/**
 * Class Restore
 * @package Aheadworks\Followupemail2\Controller\Cart
 */
class Restore extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CartRestorer
     */
    private $cartRestorer;

    /**
     * @param Context $context
     * @param CartRestorer $cartRestorer
     */
    public function __construct(
        Context $context,
        CartRestorer $cartRestorer
    ) {
        parent::__construct($context);
        $this->cartRestorer = $cartRestorer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $code = $this->getRequest()->getParam('code');
        if ($code) {
            try {
                if ($this->cartRestorer->restore($code)) {
                    return $resultRedirect->setPath('checkout/cart');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('checkout/cart');
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong code specified'));
        }

        return $resultRedirect->setPath('/');
    }
}
