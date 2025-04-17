define(['Magento_Ui/js/grid/columns/column', 'uiRegistry'], function (Abstract, registry) {
    return Abstract.extend({
        defaults: {
            bodyTmpl: 'Amasty_Affiliate/grid/account/transaction/cells/text'
        },

        getClass: function (record) {
            var cellClass = 'amasty_affiliate_gain';
            if (record.commission_plain < 0) {
                cellClass = 'amasty_affiliate_losses'
            }
            return cellClass;
        }
    });
});
