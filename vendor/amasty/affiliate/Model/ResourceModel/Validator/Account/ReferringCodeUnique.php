<?php
declare(strict_types = 1);

namespace Amasty\Affiliate\Model\ResourceModel\Validator\Account;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ReferringCodeUnique
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Check if specified referring code is unique
     *
     * @param string $referringCode
     * @param int $customerId
     * @return bool
     */
    public function isUnique(string $referringCode, int $customerId): bool
    {
        $websiteId = $this->getCustomerWebsiteId($customerId);

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['customer' => $this->getTable('customer_entity')], ['website_id'])
            ->join(
                ['affiliate_account' => $this->getTable('amasty_affiliate_account')],
                'customer.entity_id = affiliate_account.customer_id',
                ['referring_code']
            )
            ->where('customer.entity_id != ?', $customerId)
            ->where('customer.website_id = ?', $websiteId)
            ->where('affiliate_account.referring_code = ?', $referringCode);

        return !$connection->fetchOne($select);
    }

    /**
     * Get customer website Id
     *
     * @param int $customerId
     * @return string
     */
    private function getCustomerWebsiteId($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['customer' => $this->getTable('customer_entity')], ['website_id'])
            ->where('customer.entity_id = ?', $customerId);

        return $connection->fetchOne($select);
    }

    /**
     * Get resource connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Get table name
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resourceConnection->getTableName($tableName);
    }
}
