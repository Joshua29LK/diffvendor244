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
var config = {
    config: {
        mixins: {
            "Magento_Wishlist/js/add-to-wishlist" : {
                "Bss_MultiWishlist/js/add-to-wishlist": true
            }
        }
    },
    map: {
        '*': {
            'wishlist':'Bss_MultiWishlist/js/overwirte_core_wishlist',
            'wishlisttable':'Bss_MultiWishlist/js/wishlisttable'
        }
    },
    paths: {
        'bss_fancybox': 'Bss_MultiWishlist/js/jquery.bssfancybox'
    },
    shim: {
        'bss_fancybox': {
            deps: ['jquery']
        }
    }
};
require.config(config);
