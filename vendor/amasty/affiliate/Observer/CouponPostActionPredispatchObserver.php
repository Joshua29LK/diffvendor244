<?php

namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Model\Validator\AffiliateCouponValidator;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CouponPostActionPredispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    /**
     * @var AffiliateCouponValidator
     */
    private $affiliateCouponValidator;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ActionFlag $actionFlag,
        AffiliateCouponValidator $affiliateCouponValidator
    ) {
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->url = $url;
        $this->actionFlag = $actionFlag;
        $this->affiliateCouponValidator = $affiliateCouponValidator;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getRequest();
        /** @var CouponPost $controller */
        $controller = $observer->getControllerAction();

        $couponCode = $request->getParam('remove') == 1
            ? ''
            : trim($request->getParam('coupon_code'));

        if ($couponCode && !$this->affiliateCouponValidator->validate($couponCode)) {
            $this->messageManager->addErrorMessage(
                __(
                    'The coupon code "%1" is not valid.',
                    $this->escaper->escapeHtml($couponCode)
                )
            );
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $controller->getResponse()->setRedirect(
                $this->url->getUrl('checkout/cart/index')
            );
        }

        return $this;
    }
}
