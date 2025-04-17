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
namespace Bss\CheckoutSuccessPage\Model\Config\Source;

class Type extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Cross-sell Products'), 'value' => 'cross'],
                ['label' => __('Up-sell Products'), 'value' => 'up'],
                ['label' => __('Related Products'), 'value' => 'related'],
            ];
        }
        return $this->_options;
    }
}
