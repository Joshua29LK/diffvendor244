<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Setup;

use Magento\Framework\DB\Ddl\Table as Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create Multiwishlist Table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bss_multiwishlist'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_multiwishlist')
        )->addColumn(
            'multi_wishlist_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false, 'default' => '0'],
            'Customer Id'
        )->addColumn(
            'wishlist_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Wishlist Name'
        )->setComment(
            'Bss MultiWishlist'
        )->addIndex(
            $setup->getIdxName('bss_multiwishlist', ['multi_wishlist_id']),
            ['multi_wishlist_id']
        );

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addColumn(
            $installer->getTable('wishlist_item'),
            'multi_wishlist_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 11,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Multi Wishlish Id'
            ]
        );

        $installer->endSetup();
    }
}
