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

class Display extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const TYPE_GRID = 0;
    const TYPE_SLIDER = 1;
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Grid'), 'value' => self::TYPE_GRID],
                ['label' => __('Slider'), 'value' => self::TYPE_SLIDER],
            ];
        }
        return $this->_options;
    }
}
