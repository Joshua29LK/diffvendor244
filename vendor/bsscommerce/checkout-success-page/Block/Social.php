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

class Social extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/social.phtml";

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Social constructor.
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
     * @return mixed
     */
    public function getLink()
    {
        return $this->helper->getBaseUrl();
    }
}
