<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Model\Source\BalanceChangeType;

class Withdrawal extends Transaction implements TransactionInterface
{
    /**
     * @param $requestedAmount
     */
    public function create($requestedAmount)
    {
        $currentAccount = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());

        $data = [
            'affiliate_account_id' => $currentAccount->getAccountId(),
            'commission' => -$requestedAmount,
            'type' => self::TYPE_WITHDRAWAL,
            'status' => self::STATUS_PENDING,
            'balance_change_type' => BalanceChangeType::TYPE_SUBTRACTION,
            'balance' => $this->accountRepository->getByCustomerId(
                $this->customerSession->getCustomerId()
            )->getBalance()
        ];
        $this->setData($data);

        $this->withdrawalRepository->save($this);
        $this->sendMail($requestedAmount);
    }

    public function repeat()
    {
        $this->setTransactionId(null);
        $this->setBalance($this->accountRepository->getByCustomerId($this->customerSession->getCustomerId())
            ->getBalance());

        $this->setStatus(self::STATUS_PENDING);
        $this->setUpdatedAt(null);

        $this->withdrawalRepository->save($this);
        $this->sendMail($this->getCommission());
    }

    /**
     * @param $requestedAmount
     */
    protected function sendMail($requestedAmount)
    {
        /** @var \Amasty\Affiliate\Model\Account $currentAccount */
        $currentAccount = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());

        if ($this->scopeConfig->getValue('amasty_affiliate/email/admin/withdrawal_request')) {
            $emailData = $currentAccount->getData();
            $emailData['name'] = $currentAccount->getFirstname() . ' ' . $currentAccount->getLastname();
            $emailData['amount'] = $this->priceConverter->convertToPrice($requestedAmount);
            $emailData['balance'] = $this->priceConverter->convertToPrice($currentAccount->getBalance());
            $sendToMail = $this->scopeConfig->getValue('amasty_affiliate/email/general/recipient_email');

            $this->mailsender->sendMail($emailData, Mailsender::TYPE_ADMIN_NEW_WITHDRAWAL, $sendToMail);
        }
    }
}
