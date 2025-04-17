<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Transaction;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\Mailsender;
use Amasty\Affiliate\Model\Source\BalanceChangeType;
use Amasty\Affiliate\Model\Transaction;
use Amasty\Affiliate\Model\Validator\TransactionRefundValidator;

class TransactionRefundProcessor
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var TransactionRefundValidator
     */
    private $transactionRefundValidator;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        AccountRepositoryInterface $accountRepository,
        TransactionRefundValidator $transactionRefundValidator
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->transactionRefundValidator = $transactionRefundValidator;
    }

    public function execute(Transaction $transaction, float $partToSubtract = 1): void
    {
        if (!$this->transactionRefundValidator->isCanSubtractCommission($transaction)) {
            return;
        }
        $refundAmount = $transaction->getCommission() * $partToSubtract;
        $refundTransaction = clone $transaction;

        $refundTransaction->setCommission(-$refundAmount);
        $refundTransaction->unsetData(TransactionInterface::TRANSACTION_ID);

        $account = $this->accountRepository->get($transaction->getAffiliateAccountId());
        $this->processAccount($account, $refundTransaction);

        $refundTransaction->setBalance($account->getBalance());
        $refundTransaction->setStatus(Transaction::STATUS_COMPLETED);
        $refundTransaction->setPreviousStatus(null);
        $refundTransaction->setBalanceChangeType(BalanceChangeType::TYPE_SUBTRACTION);

        $this->transactionRepository->save($refundTransaction);
        $refundTransaction->sendEmail(Mailsender::TYPE_AFFILIATE_TRANSACTION_NEW);
    }

    private function processAccount(Account $account, TransactionInterface $transaction): void
    {
        $refundAmount = $transaction->getCommission();

        if ($transaction->getStatus() == Transaction::STATUS_COMPLETED) {
            $account->setBalance($account->getBalance() + $refundAmount);
            $account->setLifetimeCommission($account->getLifetimeCommission() + $refundAmount);
            $this->accountRepository->save($account);
        } elseif ($transaction->getStatus() == Transaction::STATUS_ON_HOLD) {
            $account->setOnHold($account->getOnHold() - $refundAmount);
            $this->accountRepository->save($account);
        }
    }
}
