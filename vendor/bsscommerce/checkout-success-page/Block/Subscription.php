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
namespace Bss\CheckoutSuccessPage\Block;

class Subscription extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/subscription.phtml";

    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Order
     */
    protected $helperOrder;

    /**
     * Subscription constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\CheckoutSuccessPage\Helper\Order $helperOrder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\CheckoutSuccessPage\Helper\Order $helperOrder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperOrder = $helperOrder;
    }

    /**
     * @return mixed || bool
     */
    public function getOrder()
    {
        return $this->helperOrder->getOrder();
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
