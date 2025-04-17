<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\TransactionRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Api\Data\TransactionInterfaceFactory;
use Amasty\Affiliate\Model\Mailsender;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class TransactionRepository extends AbstractRepository implements TransactionRepositoryInterface
{

    /**
     * @var Transaction
     */
    protected $resource;

    /**
     * @var TransactionInterfaceFactory
     */
    protected $factory;

    /** @var array $entities */
    protected $entities = [];

    /**
     * @var AttributeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Transaction $resource,
        TransactionInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Data\TransactionInterface $entity
     * @return Data\TransactionInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\TransactionInterface $entity)
    {
        if ($entity->getTransactionId()) {
            $entity = $this->get($entity->getTransactionId())->addData($entity->getData());
        }

        try {
            if (($entity->getPreviousStatus() != null) && $entity->getPreviousStatus() != $entity->getStatus()) {
                $mailStatuses = [$entity::STATUS_COMPLETED, $entity::STATUS_CANCELED];
                if (in_array($entity->getStatus(), $mailStatuses)
                    && $entity->getType() != \Amasty\Affiliate\Model\Transaction::TYPE_WITHDRAWAL) {
                    $entity->sendEmail(Mailsender::TYPE_AFFILIATE_TRANSACTION_STATUS);
                }
            }

            $this->resource->save($entity);
            unset($this->entities[$entity->getTransactionId()]);
        } catch (\Exception $e) {
            if ($entity->getTransactionId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save transaction with ID %1. Error: %2',
                        [$entity->getTransactionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new transaction. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return Data\TransactionInterface
     * @throws NoSuchEntityException
     *
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Transaction $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException(__('Transaction with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * @param string $orderIncrementId
     * @param string $programId
     * @return Data\TransactionInterface
     */
    public function getByOrderProgramIds($orderIncrementId, $programId)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->collectionFactory->create();

        $transactionCollection
            ->addFieldToFilter('order_increment_id', $orderIncrementId)
            ->addFieldToFilter('program_id', $programId)
            ->setPageSize(1);

        $transaction = $this->factory->create();
        if ($transactionCollection->getSize() > 0) {
            $transaction = $this->get($transactionCollection->getFirstItem()->getTransactionId());
        }

        return $transaction;
    }

    /**
     * @param Data\TransactionInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\TransactionInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getTransactionId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove transaction with ID %1. Error: %2',
                        [$entity->getTransactionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove transaction. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
