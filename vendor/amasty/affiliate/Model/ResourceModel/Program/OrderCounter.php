<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Program;

class OrderCounter extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    public const TABLE_NAME = 'amasty_affiliate_program_order_counter';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'program_id');
    }

    /**
     * Get number of orders placed for the program and the affiliate client
     *
     * @param int $programId
     * @param int $affiliateAccountId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProgramOrderCounter(int $programId, int $affiliateAccountId): int
    {
        if (!$affiliateAccountId || !$programId) {
            return 0;
        }

        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $this->getMainTable(),
            ['order_counter']
        )->where('program_id = ?', $programId)
            ->where('affiliate_account_id = ?', $affiliateAccountId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Increase the counter of the number of orders placed for the program and the affiliate client
     *
     * @param int $programId
     * @param int $affiliateAccountId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function incrementProgramOrderCounter(int $programId, int $affiliateAccountId): void
    {
        if (!$affiliateAccountId || !$programId) {
            return;
        }

        $connection = $this->getConnection();
        $orderCounter = $this->getProgramOrderCounter($programId, $affiliateAccountId);
        $orderCounterData = [
            'program_id' => $programId,
            'affiliate_account_id' => $affiliateAccountId,
            'order_counter' => ++$orderCounter
        ];
        $connection->insertOnDuplicate($this->getMainTable(), $orderCounterData);
    }
}
