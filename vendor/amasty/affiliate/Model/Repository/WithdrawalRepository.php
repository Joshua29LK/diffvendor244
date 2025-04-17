<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Model\ResourceModel\Transaction;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\Withdrawal;
use Amasty\Affiliate\Api\Data\TransactionInterfaceFactory;
use Amasty\Affiliate\Api\Data\WithdrawalInterfaceFactory;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class WithdrawalRepository extends TransactionRepository implements \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
{
    /**
     * @var WithdrawalInterfaceFactory
     */
    private $withdrawalFactory;

    /**
     * @var Withdrawal
     */
    private $withdrawalResource;

    public function __construct(
        Withdrawal $withdrawalResource,
        WithdrawalInterfaceFactory $withdrawalFactory,
        Transaction $resource,
        TransactionInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->withdrawalResource = $withdrawalResource;
        $this->withdrawalFactory = $withdrawalFactory;
        parent::__construct($resource, $factory, $searchResultsFactory, $collectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Withdrawal $entity */
            $entity = $this->withdrawalResource->load($this->withdrawalFactory->create(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException(__('Withdrawal with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }
}
