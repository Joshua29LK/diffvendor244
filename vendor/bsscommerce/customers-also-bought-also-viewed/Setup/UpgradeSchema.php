<?php

namespace Bss\CABAV\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.4', '<=')) {
            $qtyOrderedTable = $installer->getTable('bss_also_product_qty_ordered');
            if (!$installer->tableExists($qtyOrderedTable)) {
                $table = $installer->getConnection()->newTable(
                    $qtyOrderedTable
                )->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Entity ID'
                )->addColumn(
                    'qty_ordered',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Qty Ordered'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    [],
                    'Product Id'
                )->addIndex(
                    $installer->getIdxName('bss_product_qty_ordered', ['entity_id']),
                    ['entity_id']
                )->addIndex(
                    $installer->getIdxName(
                        'bss_also_product_qty_ordered',
                        ['product_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment(
                    'Bss Qty Product Ordered'
                );
                $installer->getConnection()->createTable($table);
            }

            $qtyViewedTable = $installer->getTable('bss_also_product_qty_viewd');
            if (!$installer->tableExists($qtyViewedTable)) {
                $table = $installer->getConnection()->newTable(
                    $qtyViewedTable
                )->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Entity ID'
                )->addColumn(
                    'qty_viewed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Qty Viewed'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    [],
                    'Product Id'
                )->addIndex(
                    $installer->getIdxName('bss_also_product_qty_viewd', ['entity_id']),
                    ['entity_id']
                )->addIndex(
                    $installer->getIdxName(
                        'bss_also_product_qty_viewd',
                        ['product_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment(
                    'Bss Qty Product Viewed'
                );
                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.0.6', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_also_product_qty_ordered'),
                'also_bought_product',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Also Bought Product'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_also_product_qty_ordered'),
                'store_id',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Store Id'
                ]
            );
        }
        $installer->endSetup();
    }
}