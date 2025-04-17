<?php

namespace Amasty\Affiliate\Model\ResourceModel\Program;

use Amasty\Affiliate\Model\ResourceModel\Filter\CustomerIdAndGroupIdProgramFilter;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /** @var string $_idFieldName */
    protected $_idFieldName = 'program_id';

    /**
     * @var CustomerIdAndGroupIdProgramFilter
     */
    private $customerIdAndGroupIdProgramFilter;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CustomerIdAndGroupIdProgramFilter $customerIdAndGroupIdProgramFilter,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->customerIdAndGroupIdProgramFilter = $customerIdAndGroupIdProgramFilter;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * init collection
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\Affiliate\Model\Program::class,
            \Amasty\Affiliate\Model\ResourceModel\Program::class
        );
    }

    /**
     * @param string $ruleIds
     * @return $this
     */
    public function getProgramsByRuleIds($ruleIds)
    {
        $cartRules = str_replace(' ', '', (string)$ruleIds);
        $cartRules = explode(',', $cartRules);

        $this->addFieldToFilter('main_table.rule_id', ['in' => $cartRules]);

        return $this;
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    public function isProgramRule($ruleId)
    {
        $isProgramRule = false;

        if ($this->getProgramsByRuleIds($ruleId)->count() > 0) {
            $isProgramRule = true;
        }

        return $isProgramRule;
    }

    /**
     * @param int $isActive
     * @return $this
     */
    public function addActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('main_table.is_active', ['eq' => $isActive]);

        return $this;
    }

    /**
     * @param int $affiliateCustomerId
     * @param int $affiliateCustomerGroupId
     * @return $this
     */
    public function addCustomerAndGroupFilter($affiliateCustomerId, $affiliateCustomerGroupId)
    {
        $this->customerIdAndGroupIdProgramFilter->apply(
            $this,
            $affiliateCustomerId,
            $affiliateCustomerGroupId
        );

        return $this;
    }

    /**
     * @param int $programId
     * @return $this
     */
    public function addProgramIdFilter($programId)
    {
        $this->addFieldToFilter('program_id', ['eq' => $programId]);

        return $this;
    }

    /**
     * @param string $couponCode
     * @return $this
     */
    public function addCouponFilter($couponCode)
    {
        $this->join(
            ['amcoupon' => 'amasty_affiliate_coupon'],
            'main_table.program_id = amcoupon.program_id',
            []
        );
        $this->join(
            ['coupon' => 'salesrule_coupon'],
            'amcoupon.coupon_id = coupon.coupon_id',
            []
        );

        $this->addFieldToFilter('code', $couponCode);

        return $this;
    }

    /**
     * @param int $accountId
     * @return $this
     */
    public function addOrderCounterFilter(int $accountId)
    {
        $this->getSelect()
            ->joinLeft(
                ['oc' => $this->getTable(OrderCounter::TABLE_NAME)],
                'main_table.program_id = oc.program_id AND oc.affiliate_account_id = ' . $accountId,
                []
            )->where(
                'main_table.restrict_transactions_to_number_orders = 0' .
                    ' OR oc.order_counter IS NULL' .
                    ' OR oc.order_counter < main_table.restrict_transactions_to_number_orders'
            );

        return $this;
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['salesrule' => $this->getTable('salesrule')],
                'main_table.rule_id = salesrule.rule_id',
                ['discount_amount', 'simple_action']
            );

        return $this;
    }
}
