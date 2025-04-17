<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Validator;

use Amasty\Affiliate\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\Exception\LocalizedException;

class TransactionRefundValidator
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
     * @return bool
     * @throws LocalizedException
     */
    public function isCanSubtractCommission(Transaction $transaction): bool
    {
        $refundedSum = $this->transactionResource->getRefundedSumForTransaction(
            $transaction->getOrderIncrementId(),
            (int)$transaction->getProgramId()
        );
        return $transaction->getCommission() > $refundedSum;
    }
}
