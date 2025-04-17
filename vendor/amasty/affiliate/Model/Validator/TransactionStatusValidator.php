<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Validator;

use Amasty\Affiliate\Model\Repository\TransactionRepository;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Affiliate\Api\Data\TransactionInterface;

class TransactionStatusValidator
{
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        TransactionRepository $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $orderIncrementId
     * @param int $programId
     * @return bool
     */
    public function isCommissionAlreadyAdded(string $orderIncrementId, int $programId): bool
    {
        $this->searchCriteriaBuilder->addFilter(TransactionInterface::ORDER_INCREMENT_ID, $orderIncrementId);
        $this->searchCriteriaBuilder->addFilter(TransactionInterface::PROGRAM_ID, $programId);
        $this->searchCriteriaBuilder->addFilter(TransactionInterface::STATUS, Transaction::STATUS_COMPLETED);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return (bool)$this->transactionRepository->getList($searchCriteria)->getItems();
    }
}
