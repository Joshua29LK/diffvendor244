<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\EditOrder\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($installer->tableExists('sales_order')) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('sales_order'),
                    'mp_is_edit_order',
                    [
                        'type' => Table::TYPE_TEXT,
                        'comment' => 'MagePlaza Edit Order'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $installer->getTable('sales_order'),
                    'mp_old_order_data',
                    [
                        'type' => Table::TYPE_TEXT,
                        'comment' => 'MagePlaza Edit Order Data'
                    ]
                );
            }

            if ($installer->tableExists('sales_invoice_item')) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('sales_invoice_item'),
                    'order_item_data',
                    [
                        'type' => Table::TYPE_TEXT,
                        'comment' => 'Order Item Data'
                    ]
                );
            }

            if ($installer->tableExists('sales_creditmemo_item')) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('sales_creditmemo_item'),
                    'order_item_data',
                    [
                        'type' => Table::TYPE_TEXT,
                        'comment' => 'Order Item Data'
                    ]
                );
            }

            if ($installer->tableExists('sales_shipment_item')) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('sales_shipment_item'),
                    'order_item_data',
                    [
                        'type' => Table::TYPE_TEXT,
                        'comment' => 'Order Item Data'
                    ]
                );
            }
        }
        $installer->endSetup();
    }
}
