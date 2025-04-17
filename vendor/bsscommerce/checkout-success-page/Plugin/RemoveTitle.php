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
namespace Bss\CheckoutSuccessPage\Plugin;

class RemoveTitle
{
    /**
     * @var \Bss\CheckoutSuccessPage\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * RemoveTitle constructor.
     * @param \Bss\CheckoutSuccessPage\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\CheckoutSuccessPage\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param $result
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPageHeading($subject, $result)
    {
        $controllerName = $this->request->getControllerName();
        $frontName = $this->request->getFrontName();
        if ($this->helper->isConfigEnable('checkoutsuccesspage/general/enable')
            && ($controllerName == "onepage" || $frontName == "checkoutsuccess")) {
            return '';
        }
        return $result;
    }
}
