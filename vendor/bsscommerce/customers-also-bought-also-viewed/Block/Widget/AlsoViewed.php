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

namespace Bss\CABAV\Block\Widget;

class AlsoViewed extends Also
{
    /**
     * @var string
     */
    protected $_template = "Bss_CABAV::widget/alsoviewed.phtml";

    /**
     * @var string
     */
    protected $title = 'Who viewed this also viewed';

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnable('alsoboughtviewed/alsoviewed/enable');
    }
}
