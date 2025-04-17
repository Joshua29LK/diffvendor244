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
define([
    'jquery'
], function ($) {
    'use strict';
    return function () {
        $('.bss-wishlist-hidden').hide();
        $('.loadMore').css({"cursor":"pointer","margin": "0 auto","width": "10%","padding": "8px 10px","background-color": "#eeeeee","text-align": "center", "margin-bottom": "1%"});
        $('.showLess').css({"cursor":"pointer","margin": "0 auto","width": "10%","padding": "8px 10px","background-color": "#eeeeee","text-align": "center","margin-bottom": "1%"});
        $('.showLess').hide();
        var size_li =  0;
        var x = 20;
        var lastShow = 0;
        $(document).ready(function () {
            $('body').on('click', '.loadMore', function () {
                $('.tabs-wishlist').each(function(i) {
                    if($(this).css('display') == 'block') {
                        size_li = $(this).find(".product-items li").size();
                        $(this).find(".product-items li.bss-wishlist-hidden").size();

                        x= (x+20 <= size_li) ? x+20 : size_li;
                        $(this).find('.product-items li:lt('+x+')').show();
                        $(this).find('.showLess').show();
                        if (x == size_li) {
                            lastShow = size_li%20;
                            $(this).find('.loadMore').hide();
                        } else {
                            lastShow = 0;
                        }
                    }
                });
            });
            $('body').on('click', '.showLess', function () {
                $('.tabs-wishlist').each(function(i) {
                    if($(this).css('display') == 'block') {
                        $(this).find('.loadMore').show();
                        size_li = $(this).find(".product-items li").size();
                        if (lastShow > 0 && x !== size_li) {
                            x = x - lastShow;
                            lastShow = 0;
                        }  else {
                            x=(x-20 <20) ? 20 : x-20;
                        }
                        $(this).find('.product-items li').not(':lt('+x+')').hide();
                        if (x <= 20) {
                            $(this).find('.showLess').hide();
                        }
                    }
                });
            });
        });
    };
});
