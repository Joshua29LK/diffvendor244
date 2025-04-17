<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Setup\Patch\Data;

use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\Affiliate\Model\Source\BalanceChangeType;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ChangeWithdrawalRows implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }
    
    public function apply(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(TransactionResource::TABLE_NAME);
        
        $connection->update(
            $table,
            [
                TransactionInterface::BALANCE_CHANGE_TYPE => BalanceChangeType::TYPE_SUBTRACTION,
                TransactionInterface::COMMISSION => new \Zend_Db_Expr(TransactionInterface::COMMISSION . ' * -1')
            ],
            [
                TransactionInterface::TYPE . ' = ?' => Transaction::TYPE_WITHDRAWAL,
                TransactionInterface::BALANCE_CHANGE_TYPE . ' = ?' => BalanceChangeType::TYPE_ADDITION
            ]
        );
    }
    
    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
