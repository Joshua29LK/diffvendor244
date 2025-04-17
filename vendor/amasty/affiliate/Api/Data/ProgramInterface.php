<?php

namespace Amasty\Affiliate\Api\Data;

interface ProgramInterface
{
    public const PROGRAM_ID = 'program_id';
    public const RULE_ID = 'rule_id';
    public const NAME = 'name';
    public const WITHDRAWAL_TYPE = 'withdrawal_type';
    public const IS_ACTIVE = 'is_active';
    public const COMMISSION_VALUE = 'commission_value';
    public const RESTRICT_TRANSACTIONS_TO_NUMBER_ORDERS = 'restrict_transactions_to_number_orders';
    public const COMMISSION_PER_PROFIT_AMOUNT = 'commission_per_profit_amount';
    public const COMMISSION_VALUE_TYPE = 'commission_value_type';
    public const FROM_SECOND_ORDER = 'from_second_order';
    public const COMMISSION_TYPE_SECOND = 'commission_type_second';
    public const COMMISSION_VALUE_SECOND = 'commission_value_second';
    public const IS_LIFETIME = 'is_lifetime';
    public const FREQUENCY = 'frequency';
    public const TOTAL_SALES = 'total_sales';
    public const AVAILABLE_CUSTOMERS = 'available_customers';
    public const AVAILABLE_GROUPS = 'available_groups';
    public const COMMISSION_CALCULATION = 'commission_calculation';

    /**
     * @return int
     */
    public function getProgramId();

    /**
     * @param int $programId
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setProgramId($programId);

    /**
     * @return int|null
     */
    public function getRuleId();

    /**
     * @param int|null $ruleId
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setRuleId($ruleId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getWithdrawalType();

    /**
     * @param string $withdrawalType
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setWithdrawalType($withdrawalType);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $isActive
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setIsActive($isActive);

    /**
     * @return float|null
     */
    public function getCommissionValue();

    /**
     * @param float|null $commissionValue
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValue($commissionValue);

    /**
     * @return int|null
     */
    public function getRestrictTransactionsToNumberOrders();

    /**
     * @param int|null $restrictTransactionsToNumberOrders
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setRestrictTransactionsToNumberOrders($restrictTransactionsToNumberOrders);

    /**
     * @return float|null
     */
    public function getCommissionPerProfitAmount();

    /**
     * @param float|null $commissionPerProfitAmount
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionPerProfitAmount($commissionPerProfitAmount);

    /**
     * @return string|null
     */
    public function getCommissionValueType();

    /**
     * @param string|null $commissionValueType
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValueType($commissionValueType);

    /**
     * @return int
     */
    public function getFromSecondOrder();

    /**
     * @param int $fromSecondOrder
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setFromSecondOrder($fromSecondOrder);

    /**
     * @return string|null
     */
    public function getCommissionTypeSecond();

    /**
     * @param string|null $commissionTypeSecond
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionTypeSecond($commissionTypeSecond);

    /**
     * @return float|null
     */
    public function getCommissionValueSecond();

    /**
     * @param float|null $commissionValueSecond
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValueSecond($commissionValueSecond);

    /**
     * @return int
     */
    public function getIsLifetime();

    /**
     * @param int $isLifetime
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setIsLifetime($isLifetime);

    /**
     * @return string|null
     */
    public function getFrequency();

    /**
     * @param string|null $frequency
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setFrequency($frequency);

    /**
     * @return float|null
     */
    public function getTotalSales();

    /**
     * @param float|null $totalSales
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setTotalSales($totalSales);

    /**
     * @return string|null
     */
    public function getAvailableCustomers();

    /**
     * @param string|null $customers
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setAvailableCustomers($customers);

    /**
     * @return string|null
     */
    public function getAvailableGroups();

    /**
     * @param string|null $groups
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setAvailableGroups($groups);

    /**
     * @return \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface|null
     */
    public function getCommissionCalculation();

    /**
     * @param \Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface $commissionCalculation
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionCalculation(ProgramCommissionCalculationInterface $commissionCalculation);
}
