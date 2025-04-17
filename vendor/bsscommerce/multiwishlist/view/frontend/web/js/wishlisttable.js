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
require([
    'jquery',
    'mage/mage',
    'Magento_Customer/js/customer-data'
], function ($, mage, customerData) {
    decorateTable = $('#wishlist-table');
    $.localStorage.set('mage-cache-timeout', 0);
});
