<?php

namespace Amasty\Affiliate\Model\Transaction;

use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        CurrencyInterface $currency,
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->context = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->addGrandTotalExclTaxToSelect()->getItems();
        /** @var Transaction $transaction */
        foreach ($items as $transaction) {
            $this->loadedData[$transaction->getId()] = $transaction->getData();
            $this->loadedData[$transaction->getId()]['affiliate_url'] = $this->urlBuilder->getUrl(
                'amasty_affiliate/account/edit',
                ['id' => $transaction->getAffiliateAccountId()]
            );

            if (!$transaction->getCustomerAccountId()) {
                $this->loadedData[$transaction->getId()]['customer_link_class'] = 'inactiveLink';
            }
            $this->loadedData[$transaction->getId()]['customer_url'] = $this->urlBuilder->getUrl(
                'customer/index/edit',
                ['id' => $transaction->getCustomerAccountId()]
            );
            $this->loadedData[$transaction->getId()]['order_url'] = $this->urlBuilder->getUrl(
                'sales/order/view',
                ['order_id' => $transaction->getOrderId()]
            );

            $this->preparePrices($transaction->getTransactionId());

            $statuses = $transaction->getAvailableStatuses();
            $this->loadedData[$transaction->getId()]['status'] = $statuses[$transaction->getStatus()];
            $types = $transaction->getAvailableTypes();
            $this->loadedData[$transaction->getId()]['type'] = $types[$transaction->getType()];
        }

        return $this->loadedData;
    }

    /**
     * Add currency to price and format it
     * @param $transactionId
     */
    protected function preparePrices($transactionId)
    {
        $fieldsToPrice = [
            'commission',
            'balance',
            'discount',
            'base_grand_total',
            'base_subtotal',
            'base_tax_amount',
            'gt_excl_tax'
        ];

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', Store::DEFAULT_STORE_ID)
        );

        $currency = $this->currency->getCurrency($store->getBaseCurrencyCode());

        foreach ($fieldsToPrice as $field) {
            $this->loadedData[$transactionId][$field] =
                $currency->toCurrency(sprintf("%f", $this->loadedData[$transactionId][$field]));
        }
    }
}
