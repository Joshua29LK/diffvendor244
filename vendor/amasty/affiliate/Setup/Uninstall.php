<?php

namespace Amasty\Affiliate\Setup;

use Amasty\Affiliate\Model\ResourceModel\Account;
use Amasty\Affiliate\Model\ResourceModel\Program;
use Amasty\Affiliate\Model\ResourceModel\Program\OrderCounter;
use Amasty\Affiliate\Model\ResourceModel\ProgramCommissionCalculation;
use Amasty\Affiliate\Model\ResourceModel\Transaction;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer = $setup;

        $installer->startSetup();
        
        $this->dropTables($installer);
        $this->clearConfigData($installer);

        $installer->endSetup();
    }
    
    private function dropTables(SchemaSetupInterface $installer): self
    {
        $tablesToDrop = [
            'amasty_affiliate_lifetime',
            'amasty_affiliate_links',
            Transaction::TABLE_NAME,
            Account::TABLE_NAME,
            'amasty_affiliate_banner',
            Program::TABLE_NAME,
            ProgramCommissionCalculation::TABLE_NAME,
            OrderCounter::TABLE_NAME,
            'amasty_affiliate_coupon'
        ];

        foreach ($tablesToDrop as $table) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }
        
        return $this;
    }
    
    private function clearConfigData(SchemaSetupInterface $installer): self
    {
        $configTable = $installer->getTable('core_config_data');
        $installer->getConnection()->delete($configTable, "`path` LIKE 'amasty_affiliate%'");

        return $this;
    }
}
