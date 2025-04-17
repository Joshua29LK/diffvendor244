<?php

namespace Amasty\Affiliate\Model;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class PriceConverter
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CurrencyInterface
     */
    private $currency;

    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyInterface $currency
    ) {
        $this->storeManager = $storeManager;
        $this->currency = $currency;
    }
    /**
     * Add currency and format
     * @param $value
     * @return string
     */
    public function convertToPrice($value)
    {
        if (is_numeric($value)) {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
            $value = $this->convertPriceToCurrentCurrency($value);
            $currency = $this->currency->getCurrency($currencyCode);
            $value = $currency->toCurrency(sprintf("%f", $value));
        }

        return $value;
    }

    /**
     * @param string|int|float $price
     * @return string|float|int
     */
    public function convertPriceToCurrentCurrency($price)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $currencyCode = $store->getCurrentCurrency()->getCode();

        if ($store->getBaseCurrencyCode() != $currencyCode) {
            $price = $store->getBaseCurrency()->convert($price, $currencyCode);
        }

        return $price;
    }

    /**
     * @param string|int|float $price
     * @return string|float|int
     */
    public function convertPriceToBaseCurrency($price)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        /** @var \Magento\Directory\Model\Currency $currentCurrency */
        $currentCurrency = $store->getCurrentCurrency();

        if ($store->getBaseCurrencyCode() != $currentCurrency->getCode()) {
            $price = $price * $currentCurrency->getAnyRate($store->getBaseCurrencyCode());
        }

        return $price;
    }
}
