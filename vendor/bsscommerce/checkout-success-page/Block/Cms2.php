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

use Bss\CheckoutSuccessPage\Helper\Data;

class Cms2 extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string $_template = "Bss_CheckoutSuccessPage::checkout/cms2.phtml"
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/cms2.phtml";

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Cms2 constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCms2()
    {
        $content = $this->helper->getConfigValue('checkoutsuccesspage/cms/cms2');
        return $this->helper->getEditor($content);
    }
}
