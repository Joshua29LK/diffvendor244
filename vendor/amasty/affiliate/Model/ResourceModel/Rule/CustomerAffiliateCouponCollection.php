<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Rule;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Api\Data\CouponInterface;
use Amasty\Affiliate\Model\ResourceModel\Account;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;

class CustomerAffiliateCouponCollection extends Collection
{
    public function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['ac' => $this->getTable(Account::TABLE_NAME)],
                'main_table.' . CouponInterface::ACCOUNT_ID . ' = ac.' . AccountInterface::ACCOUNT_ID,
                []
            )->joinRight(
                ['o' => $this->getTable('sales_order')],
                'salesrule_coupon.code = o.coupon_code',
                []
            )->distinct(true);

        return $this;
    }
}
