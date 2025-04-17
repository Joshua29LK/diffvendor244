<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\CommissionCalculation;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AvailableCommissionCalculator
{
    public const INCLUDE_SHIPPING_CONFIG_KEY = 'amasty_affiliate/commission_calculation/include_shipping';
    public const INCLUDE_TAX_CONFIG_KEY = 'amasty_affiliate/commission_calculation/include_tax';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetFilteredOrderItems
     */
    private $getFilteredOrderItems;

    /**
     * @var array
     */
    private $invalidProductTypes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GetFilteredOrderItems $getFilteredOrderItems,
        array $invalidProductTypes
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->getFilteredOrderItems = $getFilteredOrderItems;
        $this->invalidProductTypes = $invalidProductTypes;
    }

    /**
     * Calculate available commission from order depending on Program configuration and config
     *
     * @param ProgramInterface $program
     * @param OrderInterface $order
     *
     * @return float
     */
    public function calculate(ProgramInterface $program, OrderInterface $order): float
    {
        $includeShipping = $this->scopeConfig->isSetFlag(self::INCLUDE_SHIPPING_CONFIG_KEY);
        $includeTax = $this->scopeConfig->isSetFlag(self::INCLUDE_TAX_CONFIG_KEY);
        $orderItems = $order->getItems();

        if ($program->getCommissionCalculation() && $program->getCommissionCalculation()->getIsEnabled()) {
            $orderItems = $this->getFilteredOrderItems->execute(
                $program->getCommissionCalculation(),
                $orderItems
            );
        }
        $commission = .0;

        foreach ($orderItems as $item) {
            if (in_array($item->getProductType(), $this->invalidProductTypes)) {
                continue;
            }
            if ($item->hasParentItem()) {
                $parentItem = $item->getParentItem();
                if ($parentItem->getProductType() == 'configurable') {
                    $item = $parentItem;
                }
            }

            if ($includeTax) {
                $commission += $item->getBaseRowTotal()
                    + $item->getBaseTaxAmount()
                    - $item->getBaseDiscountAmount()
                    + $item->getBaseDiscountTaxCompensationAmount()
                    + $item->getBaseWeeeTaxAppliedRowAmnt();
            } else {
                $commission += $item->getBaseRowTotal()
                    - $item->getBaseDiscountAmount();
            }
        }

        if ($includeTax && $includeShipping) {
            $commission += $order->getBaseShippingInclTax();
        } elseif (!$includeTax && $includeShipping) {
            $commission += $order->getBaseShippingAmount()
                + $order->getBaseShippingDiscountTaxCompensationAmnt();
        }

        return $commission;
    }
}
