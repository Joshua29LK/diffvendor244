<?php

namespace Amasty\Affiliate\Model\ResourceModel\Withdrawal;

use Amasty\Affiliate\Model\ResourceModel\Transaction;

class Collection extends Transaction\Collection
{
    /**
     * @return $this|Transaction\Collection|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter('type', ['eq' => \Amasty\Affiliate\Model\Transaction::TYPE_WITHDRAWAL]);

        return $this;
    }

    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);

        return $this;
    }

    public function getCurrentAccountPendingAmount()
    {
        $this->addFieldToFilter(
            'main_table.affiliate_account_id',
            ['eq' => $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId())->getAccountId()]
        );
        $this->addFieldToFilter('main_table.status', ['eq' => \Amasty\Affiliate\Model\Withdrawal::STATUS_PENDING]);
        $this->getSelect()->columns(['pending' => 'SUM(commission)']);
        $pending = $this->getFirstItem()->getPending();

        return $pending;
    }
}
