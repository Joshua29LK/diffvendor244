<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Transaction;

use Amasty\Affiliate\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\Exception\LocalizedException;

class RefundCalculator
{
    /**
     * @var TransactionResource
     */
    private $transactionResource;

    public function __construct(
        TransactionResource $transactionResource
    ) {
        $this->transactionResource = $transactionResource;
    }

    /**
     * @param Transaction $transaction
     * @return float
     * @throws LocalizedException
     */
    public function calculatePartToSubtract(Transaction $transaction): float
    {
        $alreadyRefundedAmount = $this->transactionResource->getRefundedSumForTransaction(
            $transaction->getOrderIncrementId(),
            (int)$transaction->getProgramId()
        );

        return (float)($transaction->getCommission() - $alreadyRefundedAmount) / $transaction->getCommission();
    }
}
