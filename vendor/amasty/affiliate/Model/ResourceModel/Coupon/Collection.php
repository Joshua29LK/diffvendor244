<?php

namespace Amasty\Affiliate\Model\ResourceModel\Coupon;

use Amasty\Affiliate\Model\ResourceModel\Filter\CustomerIdAndGroupIdProgramFilter;
use Amasty\Affiliate\Model\ResourceModel\Program;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
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
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\Affiliate\Model\Coupon::class,
            \Amasty\Affiliate\Model\ResourceModel\Coupon::class
        );
    }

    /**
     * Add required data to select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['salesrule_coupon' => $this->getTable('salesrule_coupon')],
                'main_table.coupon_id = salesrule_coupon.coupon_id',
                ['code']
            )
            ->joinLeft(
                ['amasty_affiliate_program' => $this->getTable(Program::TABLE_NAME)],
                'main_table.program_id = amasty_affiliate_program.program_id',
                ['name']
            );

        return $this;
    }

    /**
     * @param $programId
     * @param $accountId
     * @return $this
     */
    public function addFilterForCoupon($programId, $accountId)
    {
        $this
            ->addFieldToFilter('main_table.program_id', ['eq' => $programId])
            ->addFieldToFilter('main_table.account_id', ['eq' => $accountId]);

        return $this;
    }

    /**
     * @param $coupon
     * @return $this
     */
    public function addCouponFilter($coupon)
    {
        $this->addFieldToFilter('code', ['eq' => $coupon]);

        return $this;
    }

    /**
     * @param string $coupon
     * @return bool
     */
    public function isAffiliateCoupon($coupon)
    {
        $isAffiliateCoupon = false;

        $this->addCouponFilter($coupon);
        if ($this->getSize() > 0) {
            $isAffiliateCoupon = true;
        }

        return $isAffiliateCoupon;
    }

    /**
     * @param int $isActive
     * @return $this
     */
    public function addProgramActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('amasty_affiliate_program.is_active', ['eq' => $isActive]);

        return $this;
    }

    /**
     * @param int $affiliateCustomerId
     * @param int $affiliateCustomerGroupId
     * @return $this
     */
    public function addProgramCustomerAndGroupIdFilter($affiliateCustomerId, $affiliateCustomerGroupId)
    {
        $this->customerIdAndGroupIdProgramFilter->apply(
            $this,
            $affiliateCustomerId,
            $affiliateCustomerGroupId,
            Program::TABLE_NAME
        );
        return $this;
    }

    /**
     * @param $accountId
     * @return $this
     */
    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('account_id', ['eq' => $accountId]);

        return $this;
    }
}
