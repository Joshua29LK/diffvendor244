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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CABAV\Model\Config\Source;

class SortViewed extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const ORDER_RANDOM = 0;
    const ORDER_AZ = 1;
    const ORDER_ZA = 2;
    const ORDER_HIGHLOW = 3;
    const ORDER_LOWHIGH = 4;
    const ORDER_QTY = 5;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Random'), 'value' => self::ORDER_RANDOM],
                ['label' => __('Product Name (A-Z)'), 'value' => self::ORDER_AZ],
                ['label' => __('Product Name (Z-A)'), 'value' => self::ORDER_ZA],
                ['label' => __('Product Price (High-Low)'), 'value' => self::ORDER_HIGHLOW],
                ['label' => __('Product Price (Low-High)'), 'value' => self::ORDER_LOWHIGH],
                ['label' => __('Viewed Times of Product'), 'value' => self::ORDER_QTY]
            ];
        }
        return $this->_options;
    }
}
