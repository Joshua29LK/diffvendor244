<?php

namespace Amasty\Affiliate\Model\ResourceModel;

use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Model\Source\BalanceChangeType;
use Amasty\Affiliate\Model\Transaction as TransactionModel;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;

class Transaction extends AbstractDb
{
    public const TABLE_NAME = 'amasty_affiliate_transaction';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var DateTime
     */
    protected $date;

    public function __construct(
        Context $context,
        EntityManager $entityManager,
        Order $order,
        DateTime $date,
        $connectionName = null
    ) {
        $this->order = $order;
        $this->date = $date;
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, TransactionInterface::TRANSACTION_ID);
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return \Amasty\Affiliate\Model\Transaction
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        /** @var \Amasty\Affiliate\Model\Transaction $loadedObject */
        $loadedObject = $this->entityManager->load($object, $value);

        return $loadedObject;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->gmtTimestamp());
        return parent::save($object);
    }

    /**
     * @param string $orderIncrementId
     * @param int $programId
     * @return float
     * @throws LocalizedException
     */
    public function getRefundedSumForTransaction(string $orderIncrementId, int $programId): float
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->columns(['refunded' => 'SUM(commission)'])
            ->where('order_increment_id = ?', $orderIncrementId)
            ->where('type = ?', TransactionModel::TYPE_PER_SALE)
            ->where('program_id = ?', $programId)
            ->where('balance_change_type = ?', BalanceChangeType::TYPE_SUBTRACTION)
            ->group('order_increment_id');

        $data = $this->getConnection()->fetchRow($select);
        if ($data) {
            return -(float)$data['refunded'];
        }

        return .0;
    }
}
