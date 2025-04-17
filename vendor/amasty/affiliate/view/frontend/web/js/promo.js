define([
    'jquery',
    'Amasty_Affiliate/js/clipboard.min'
], function ($, clipboard) {
    'use strict';

    $.widget('mage.affilatePromo', {
        _init: function () {
            var clip,
                clipPreview,
                clipLink,
                clipWidget;

            clip = new clipboard('[data-amaffiliate-js="copy-button"]');
            clip.on('success', this.toggleCopiedMessage.bind(this));

            clipPreview = new clipboard('[data-amaffiliate-js="copy-button-preview"]', {
                text: function (trigger) {
                    return $(trigger).closest('td').prev('td').html();
                }
            });
            clipPreview.on('success', function (e) {
                toggleButton(e.trigger);
            });

            clipLink = new clipboard('[data-amaffiliate-js="copy-button-link"]');
            clipLink.on('success', function (e) {
                toggleButton(e.trigger);
            });

            clipWidget = new clipboard('[data-amaffiliate-js="copy-widget"]', {
                text: function (trigger) {
                    return $('[data-amaffiliate-js="affiliate-widget"]').parent().html().replace(/&amp;/g, '&');
                }
            });
            clipWidget.on('success', function (e) {
                toggleButton(e.trigger);
            });

            $('[data-amaffiliate-js="input-link"]').keyup(function () {
                var link,
                    params;

                link = $('[data-amaffiliate-js="input-link"]').val();
                params = $('[data-amaffiliate-js="link-params"]').text();

                if (link == '') {
                    params = '';
                } else if (link.indexOf('?') == -1) {
                    params = '?' + params;
                } else {
                    params = '&' + params;
                }

                $('[data-amaffiliate-js="affiliate-link"]').text(link + params);
            });

            function toggleButton(button) {
                button.innerText = $('[data-amaffiliate-js="copied-text"]').text();

                $(button).removeClass('action').removeClass('primary');
                $(button).addClass('copied').show();

                setTimeout(function () {
                    button.innerText = $('[data-amaffiliate-js="copy-text"]').text();
                    $(button).addClass('action').addClass('primary');
                    $(button).removeClass('copied');
                }, 500);
            }
        },

        toggleCopiedMessage: function (event) {
            var button = $(event.trigger);

            button.addClass('-copied');
            setTimeout(function () {
                button.removeClass('-copied');
            }, 1000);
        }
    });

    return $.mage.affilatePromo;
});
