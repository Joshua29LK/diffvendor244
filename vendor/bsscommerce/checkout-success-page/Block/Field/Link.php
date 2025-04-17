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
namespace Bss\CheckoutSuccessPage\Block\Field;

class Link extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Link constructor.
     * @param \Magento\Framework\Url $frontUrlModel
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Url $frontUrlModel,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->frontUrlModel = $frontUrlModel;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $link = $this->getFrontendLink();
        $html = '<a href="' . $link . '" target="_blank">'
        . __('Test checkout success page in a new window')
        . '</a>';
        return $html;
    }
    public function getFrontendLink()
    {
        return $this->frontUrlModel->getUrl('checkoutsuccess/test/success', null);
    }
}
