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
namespace Bss\CheckoutSuccessPage\Block\Backend;

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');
        $html .= '<script type="text/javascript">
        require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
            $(document).ready(function () {
                var $el = $("#' . $element->getHtmlId() . '");
                $el.css("backgroundColor", "'. $value .'");
                $el.ColorPicker({
                    color: "'. $value .'",
                    onChange: function (hsb, hex, rgb) {
                        $el.css("backgroundColor", "#" + hex).val("#" + hex);
                    }
                });
            });
        });
        </script>';
        return $html;
    }
}
