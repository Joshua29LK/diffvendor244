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
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    return function (widget) {
        window.checkIsFileUploaded = false;
        $.widget('mage.addToWishlist', widget, {
            bindFormSubmit: function () {
                var self = this;

                $('[data-action="add-to-wishlist"]').on('click', function (event) {
                    var element, params, form;

                    element = $('input[type=file]' + self.options.customOptionsInfo);
                    params = $(event.currentTarget).data('post');
                    form = $(element).closest('form');
                    window.checkIsFileUploaded = true;

                    if (params.data.id) {
                        $('<input>', {
                            type: 'hidden',
                            name: 'id',
                            value: params.data.id
                        }).appendTo(form);
                    }
                });
            }
        });
        return $.mage.addToWishlist;
    };
});
