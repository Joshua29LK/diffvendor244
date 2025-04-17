<?php

namespace Amasty\Affiliate\Plugin\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;

class DataProvider
{
    public function beforeAddFilter(
        \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider $subject,
        Filter $filter
    ) {
        if ($subject->getName() == 'amasty_affiliate_transaction_listing_data_source'
            || $subject->getName() == 'amasty_affiliate_withdrawal_listing_data_source'
            || $subject->getName() == 'amasty_affiliate_account_transaction_listing_data_source'
            || $subject->getName() == 'amasty_affiliate_account_withdrawal_listing_data_source'
        ) {
            if (0 === strpos($filter->getField(), 'updated_at')) {
                $filter->setField('main_table.updated_at');
            }
            if (0 === strpos($filter->getField(), 'status')) {
                $filter->setField('main_table.status');
            }
        }
        if ($subject->getName() == 'amasty_affiliate_program_listing_data_source') {
            if (0 === strpos($filter->getField(), 'name')) {
                $filter->setField('main_table.name');
            }
            if (0 === strpos($filter->getField(), 'is_active')) {
                $filter->setField('main_table.is_active');
            }
        }
    }
}
