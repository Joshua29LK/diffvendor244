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

define(
    [
        'jquery'
    ],
    function ($) {
        return function (config, element) {
            var data = config.data,
                url = config.url,
                type = config.type,
                urlSave = config.urlsave,
                currentProduct = config.currentproduct;

            $.ajax({
                showLoader: false,
                url: url,
                data: data,
                type: "POST",
                dataType: 'json'
            }).done(function (result) {
                $(element).html(result.html);
                $(element).trigger('contentUpdated');
            });

            if (type == 'alsoview' && currentProduct != '') {
                $.ajax({
                    showLoader: false,
                    url: urlSave,
                    data: {id: currentProduct},
                    type: "POST",
                    dataType: 'json'
                }).done(function (result) {
                    return;
                });
            }
            if ($(".footer .links .widget").find(".alsobought-list").length > 0 || $(".footer .links .widget").find(".alsoview-list").length > 0 ){
                $(".footer .links").addClass('also');
            }else {
                $(".footer .links").removeClass('also');
            }
        }

    }
);