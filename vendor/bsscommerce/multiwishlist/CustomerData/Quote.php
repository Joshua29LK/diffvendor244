<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class Quote
 *
 * @package Bss\MultiWishlist\CustomerData
 */
class Quote implements SectionSourceInterface
{
    /**
     * { @inheritdoc }
     */
    public function getSectionData()
    {
        return [];
    }
}
