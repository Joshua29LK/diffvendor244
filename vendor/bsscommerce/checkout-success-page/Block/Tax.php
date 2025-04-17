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

class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * Tax helper
     * @var \Magento\Tax\Helper\Data
     */
    protected $helper;

    /**
     * Tax Constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Get Helper Tax
     * @return \Magento\Tax\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
