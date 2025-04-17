<?php

namespace Amasty\Affiliate\Plugin\Sales\Model;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\ProgramRepositoryInterface;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Coupon;
use Amasty\Affiliate\Model\ResourceModel\Coupon\Collection;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Amasty\Affiliate\Model\Transaction\AddValidator;
use Amasty\Affiliate\Model\Transaction\RefundCalculator;
use Amasty\Affiliate\Model\Transaction\TransactionRefundProcessor;
use Amasty\Affiliate\Model\Validator\TransactionStatusValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order as OrderModel;

class Order
{
    /**
     * Key for the processed order ID
     */
    public const PROCESSED_ORDER_ID = 'processed_order_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $programsCollectionFactory;

    /**
     * @var TransactionCollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var AccountRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var TransactionRefundProcessor
     */
    private $transactionRefundProcessor;

    /**
     * @var ProgramRepositoryInterface
     */
    private $programRepository;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Collection
     */
    private $couponCollection;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var AddValidator
     */
    private $addValidator;

    /**
     * @var RefundCalculator
     */
    private $refundCalculator;

    /**
     * @var TransactionStatusValidator
     */
    private $transactionStatusValidator;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $programsCollectionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        AccountRepositoryInterface $accountRepository,
        ProgramRepositoryInterface $programRepository,
        TransactionRepositoryInterface $transactionRepository,
        TransactionRefundProcessor $transactionRefundProcessor,
        Coupon $coupon,
        Collection $couponCollection,
        Registry $registry,
        AddValidator $addValidator,
        RefundCalculator $refundCalculator,
        TransactionStatusValidator $transactionStatusValidator
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->programsCollectionFactory = $programsCollectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transactionRefundProcessor = $transactionRefundProcessor;
        $this->programRepository = $programRepository;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
        $this->coreRegistry = $registry;
        $this->addValidator = $addValidator;
        $this->refundCalculator = $refundCalculator;
        $this->transactionStatusValidator = $transactionStatusValidator;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param \Magento\Sales\Model\Order $result
     * @return mixed
     */
    public function afterSetStatus($subject, $result)
    {
        $orderStatus = $result->getStatus();

        if (!$this->canMakeChangesWithTransaction($result)) {
            return $result;
        }

        $addStatus = $this->scopeConfig->getValue('amasty_affiliate/commission/add_commission_status');

        $subtractStatuses = explode(
            ',',
            $this->scopeConfig->getValue('amasty_affiliate/commission/subtract_commission_status')
        );
        if (in_array($orderStatus, $subtractStatuses)) {
            $this->coreRegistry->register(self::PROCESSED_ORDER_ID, $result->getIncrementId(), true);

            /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
            $transactions = $this->transactionCollectionFactory->create()
                ->addIncrementIdFilter($result->getIncrementId())
                ->addTypeFilter(\Amasty\Affiliate\Model\Transaction::TYPE_PER_SALE);

            /** @var \Amasty\Affiliate\Model\Transaction $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->getType() == $transaction::TYPE_FOR_FUTURE_PER_PROFIT) {
                    $transaction = $transaction->getPerProfitTransaction($transaction);
                }

                if ($transaction->getStatus() == $transaction::STATUS_COMPLETED) {
                    $this->transactionRefundProcessor->execute(
                        $transaction,
                        $this->refundCalculator->calculatePartToSubtract($transaction)
                    );
                } else {
                    if ($transaction->getStatus() == $transaction::STATUS_ON_HOLD) {
                        /** @var \Amasty\Affiliate\Model\Account $account */
                        $account = $this->accountRepository->get($transaction->getAffiliateAccountId());
                        $account->setOnHold($account->getOnHold() - $transaction->getCommission());
                        $this->accountRepository->save($account);
                    }

                    $transaction->setCommission(0);
                    $transaction->setStatus($transaction::STATUS_CANCELED);
                    $this->transactionRepository->save($transaction);
                }
            }
        }

        /** @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programs */
        $programs = $this->programsCollectionFactory->create()->getProgramsByRuleIds($result->getAppliedRuleIds());

        $couponCode = $result->getCouponCode();
        if ($couponCode
            && $this->couponCollection->isAffiliateCoupon($couponCode)
            && $this->coupon->getProgramId($couponCode)
        ) {
            $programs->addProgramIdFilter($this->coupon->getProgramId($couponCode));
        }

        if ($programs->getItems() && $orderStatus == $addStatus) {
            $this->coreRegistry->register(self::PROCESSED_ORDER_ID, $result->getIncrementId(), true);
        }

        /** @var \Amasty\Affiliate\Model\Program $program */
        foreach ($programs as $program) {
            if ($program->getIsActive()) {
                if ($orderStatus == $addStatus
                    && $result->getBaseSubtotalRefunded() == 0
                    && !$this->transactionStatusValidator->isCommissionAlreadyAdded(
                        $result->getIncrementId(),
                        $program->getId()
                    )
                ) {
                    $program->addTransaction($result, \Amasty\Affiliate\Model\Transaction::STATUS_COMPLETED);
                }
                if ($result->getState() == OrderModel::STATE_COMPLETE) {
                    $program->setTotalSales($program->getTotalSales() + $result->getBaseSubtotal());
                    $this->programRepository->save($program);
                }
            }
        }

        return $result;
    }

    /**
     * @param OrderModel $result
     * @return bool
     */
    public function canMakeChangesWithTransaction(OrderModel $result): bool
    {
        if (!$result->getIsInProcess()
            && $this->coreRegistry->registry(self::PROCESSED_ORDER_ID)
            && ($this->coreRegistry->registry(self::PROCESSED_ORDER_ID) === $result->getIncrementId())
        ) {
            return false;
        }

        if (!$this->addValidator->canAddTransaction($result)) {
            return false;
        }

        return true;
    }
}
