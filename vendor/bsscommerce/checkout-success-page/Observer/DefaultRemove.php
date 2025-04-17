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
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DefaultRemove implements ObserverInterface
{
    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Data
     */
    protected $helper;

    /**
     * DefaultRemove constructor.
     * @param \Bss\CheckoutSuccessPage\Helper\Data $helper
     */
    public function __construct(
        \Bss\CheckoutSuccessPage\Helper\Data $helper
    ) {

        $this->helper = $helper;
    }
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $layout = $event->getLayout();
        if ($this->helper->isConfigEnable('checkoutsuccesspage/general/enable')) {
            $layout->unsetElement('checkout.success');
            $layout->unsetElement('checkout.registration');
        }
    }
}
