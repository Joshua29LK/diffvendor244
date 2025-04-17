
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';
    $.widget('mage.menu',$.ui.menu, {});
    return $.mage.menu;
});