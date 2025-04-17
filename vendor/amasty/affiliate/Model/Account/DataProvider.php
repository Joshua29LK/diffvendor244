<?php

namespace Amasty\Affiliate\Model\Account;

use Amasty\Affiliate\Model\ResourceModel\Account\Collection;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Amasty\Affiliate\Model\Account;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\Store;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public const BALANCE = 'balance';
    public const LIFETIME_COMMISSION = 'lifetime_commission';
    public const COMMISSION_PAID = 'commission_paid';
    public const ON_HOLD = 'on_hold';

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

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

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param UrlInterface $urlBuilder
     * @param CurrencyInterface $currency
     * @param StoreManagerInterface $storeManager
     * @param ContextInterface $context
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        UrlInterface $urlBuilder,
        CurrencyInterface $currency,
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->context = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Account $account */
        foreach ($items as $account) {
            $this->loadedData[$account->getId()] = $account->getData();
            $this->loadedData[$account->getId()]['customer_url'] = $this->urlBuilder->getUrl(
                'customer/index/edit',
                ['id' => $account->getCustomerId()]
            );

            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', Store::DEFAULT_STORE_ID)
            );

            /**
             * TODO: Refactor UI form for fields balance and another with currencies.
             */
            $currency = $this->currency->getCurrency($store->getBaseCurrencyCode());
            $this->loadedData[$account->getAccountId()][self::BALANCE] =
                $currency->toCurrency(sprintf("%f", $this->loadedData[$account->getAccountId()][self::BALANCE]));
            $this->loadedData[$account->getAccountId()][self::LIFETIME_COMMISSION] =
                $currency->toCurrency(
                    sprintf(
                        "%f",
                        $this->loadedData[$account->getAccountId()][self::LIFETIME_COMMISSION]
                    )
                );
            $this->loadedData[$account->getAccountId()][self::COMMISSION_PAID] =
                $currency->toCurrency(
                    sprintf(
                        "%f",
                        $this->loadedData[$account->getAccountId()][self::COMMISSION_PAID]
                    )
                );
            $this->loadedData[$account->getAccountId()][self::ON_HOLD] =
                $currency->toCurrency(
                    sprintf(
                        "%f",
                        $this->loadedData[$account->getAccountId()][self::ON_HOLD]
                    )
                );
        }

        $data = $this->dataPersistor->get('amasty_affiliate_account');
        if (!empty($data)) {
            $account = $this->collection->getNewEmptyItem();
            $account->setData($data);
            $this->loadedData[$account->getId()] = $account->getData();
            $this->dataPersistor->clear('amasty_affiliate_account');
        }

        return $this->loadedData;
    }
}
