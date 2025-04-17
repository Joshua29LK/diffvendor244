<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Plugin\Sales\Model\Service;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Api\ProgramRepositoryInterface;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Model\Source\BalanceChangeType;
use Amasty\Affiliate\Model\Transaction;
use Amasty\Affiliate\Model\Transaction\TransactionRefundProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoService
{
    public const SUBTRACT_MEMO_CONFIG_PATH = 'amasty_affiliate/commission/subtract_creditmemo';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProgramRepositoryInterface
     */
    private $programRepository;

    /**
     * @var CollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * @var TransactionRefundProcessor
     */
    private $transactionRefundProcessor;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        AccountRepositoryInterface $accountRepository,
        ScopeConfigInterface $scopeConfig,
        ProgramRepositoryInterface $programRepository,
        CollectionFactory $transactionCollectionFactory,
        TransactionRefundProcessor $transactionRefundProcessor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->programRepository = $programRepository;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->transactionRefundProcessor = $transactionRefundProcessor;
    }

    /**
     * @param \Magento\Sales\Model\Service\CreditmemoService $subject
     * @param Creditmemo $result
     * @return Creditmemo
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRefund(
        \Magento\Sales\Model\Service\CreditmemoService $subject,
        Creditmemo $result
    ): Creditmemo {
        if ($this->scopeConfig->getValue(self::SUBTRACT_MEMO_CONFIG_PATH)) {
            $order = $result->getOrder();
            $transactions = $this->transactionCollectionFactory->create()
                ->addIncrementIdFilter($order->getIncrementId())
                ->addTypeFilter(Transaction::TYPE_PER_SALE)
                ->addFieldToFilter(TransactionInterface::BALANCE_CHANGE_TYPE, BalanceChangeType::TYPE_ADDITION);

            /** @var Transaction $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->getStatus() != Transaction::STATUS_CANCELED) {
                    $this->transactionRefundProcessor->execute(
                        $transaction,
                        $this->calculatePartToSubtract($result, $order)
                    );

                    $program = $this->programRepository->get($transaction->getProgramId());
                    $program->setTotalSales($program->getTotalSales() - $order->getBaseTotalRefunded());

                    $this->programRepository->save($program);
                }
            }
        }

        return $result;
    }

    private function calculatePartToSubtract(Creditmemo $creditmemo, Order $order): float
    {
        $fullOrderAmount = $order->getBaseSubtotal() + $order->getBaseDiscountAmount();
        $currentRefundedAmount = $creditmemo->getBaseSubtotal() + $creditmemo->getBaseDiscountAmount();

        return $currentRefundedAmount / $fullOrderAmount;
    }
}
