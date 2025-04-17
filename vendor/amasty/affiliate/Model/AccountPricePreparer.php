<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory;

class AccountPricePreparer
{

    /**
     * @var PriceConverter
     */
    private $priceConverter;

    /**
     * @var ResourceModel\Withdrawal\CollectionFactory
     */
    private $withdrawalCollectionFactory;

    public function __construct(
        PriceConverter $priceConverter,
        CollectionFactory $withdrawalCollectionFactory
    ) {
        $this->priceConverter = $priceConverter;
        $this->withdrawalCollectionFactory = $withdrawalCollectionFactory;
    }

    /**
     * Add currency and format
     *
     * @param Account $account
     */
    public function preparePrices($account)
    {
        $priceFields = [
            'balance',
            'available',
            'on_hold',
            'commission_paid',
            'lifetime_commission'
        ];

        $priceData = [];

        foreach ($priceFields as $field) {
            $priceData[$field . '_with_currency'] = $this->priceConverter->convertToPrice($account->getData($field));
            $withdrawalCollection = $this->withdrawalCollectionFactory->create();
            $pending = (float)$withdrawalCollection->getCurrentAccountPendingAmount();
            $balance = $account->getData('balance');
            $onHold = $account->getData('on_hold');
            if ($field == 'balance') {
                $priceData[$field . '_with_currency'] =
                    $this->priceConverter->convertToPrice($balance + $onHold);
            }
            if ($field == 'available') {
                $available = $account->getData('balance') - abs($pending);
                $priceData[$field . '_with_currency'] = $this->priceConverter->convertToPrice($available);
            }
        }

        $account->addData($priceData);
    }
}
