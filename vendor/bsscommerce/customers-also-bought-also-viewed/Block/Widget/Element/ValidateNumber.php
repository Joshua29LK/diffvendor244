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
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Block\Widget\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ValidateNumber
 *
 * @package Bss\CABAV\Block\Widget\Element
 */
class ValidateNumber extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->setType('text');
        $element->addClass('validate-digits validate-greater-than-zero');
        return parent::render($element);
    }
}
