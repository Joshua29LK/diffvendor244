<?php

namespace Wyomind\SimpleGoogleShopping\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Init implements SchemaPatchInterface, PatchRevertableInterface
{

    const version = "1.0.0";

    /**
     * @var SchemaSetupInterface
     */
    protected $schemaSetup;
    private $dbVersion = 0;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        ModuleResource       $moduleResource
    ) {

        $this->schemaSetup = $schemaSetup;
        $this->dbVersion = $moduleResource->getDbVersion("Wyomind_SimpleGoogleShopping");
    }


    public function apply()
    {

        if (version_compare($this->dbVersion, self::version) >= 0) {
            $this->schemaSetup->getConnection()->startSetup();
            $installer = $this->schemaSetup;

            $simplegoogleshoppingTable = $installer->getConnection()
                ->newTable($installer->getTable('simplegoogleshopping_feeds'))
                ->addColumn(
                    'simplegoogleshopping_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Data Feed ID'
                )
                ->addColumn(
                    'simplegoogleshopping_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Data Feed Name'
                )
                ->addColumn(
                    'simplegoogleshopping_filename',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'simple'],
                    'Data Feed Filename'
                )
                ->addColumn(
                    'simplegoogleshopping_path',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => 'simple'],
                    'Data Feed File path'
                )
                ->addColumn(
                    'simplegoogleshopping_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Data Feed Last Update Time'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Data Feed Associated Store ID'
                )
                ->addColumn(
                    'simplegoogleshopping_url',
                    Table::TYPE_TEXT,
                    120,
                    ['nullable' => true, 'default' => 'simple'],
                    'Data Feed Website Url'
                )
                ->addColumn(
                    'simplegoogleshopping_title',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Title'
                )
                ->addColumn(
                    'simplegoogleshopping_description',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Description'
                )
                ->addColumn(
                    'simplegoogleshopping_xmlitempattern',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed XML Item Pattern'
                )
                ->addColumn(
                    'simplegoogleshopping_categories',
                    Table::TYPE_TEXT,
                    '16M',
                    [],
                    'Data Feed Categories Selection'
                )
                ->addColumn(
                    'simplegoogleshopping_category_filter',
                    Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Data Feed Categories Inclusion Type'
                )
                ->addColumn(
                    'simplegoogleshopping_category_type',
                    Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Data Feed Categories Filter (product/parent)'
                )
                ->addColumn(
                    'simplegoogleshopping_type_ids',
                    Table::TYPE_TEXT,
                    150,
                    [],
                    'Data Feed Product Types Selection'
                )
                ->addColumn(
                    'simplegoogleshopping_visibility',
                    Table::TYPE_TEXT,
                    150,
                    [],
                    'Data Feed Product Visibilities Selection'
                )
                ->addColumn(
                    'simplegoogleshopping_attribute_sets',
                    Table::TYPE_TEXT,
                    250,
                    ['default' => '*'],
                    'Data Feed Attribute Sets Selection'
                )
                ->addColumn(
                    'simplegoogleshopping_attributes',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Advanced Filters'
                )
                ->addColumn(
                    'simplegoogleshopping_report',
                    Table::TYPE_TEXT,
                    4000,
                    [],
                    'Data Feed Last Report'
                )
                ->addColumn(
                    'cron_expr',
                    Table::TYPE_TEXT,
                    900,
                    [],
                    'Data Feed Schedule Task'
                )
                ->addColumn(
                    'simplegoogleshopping_feed_taxonomy',
                    Table::TYPE_TEXT,
                    150,
                    ['default' => '[default] en_US.txt'],
                    'Data Feed Taxonomies File'
                )
                ->addIndex(
                    $installer->getIdxName('simplegoogleshopping_feeds', ['simplegoogleshopping_id']),
                    ['simplegoogleshopping_id']
                )
                ->setComment('Simple Google Shopping Data Feeds Table');

            $installer->getConnection()->createTable($simplegoogleshoppingTable);

            $simplegoogleshoppingFunctionsTable = $installer->getConnection()
                ->newTable($installer->getTable('simplegoogleshopping_functions'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Data Feed ID'
                )
                ->addColumn(
                    'script',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Custom Function Script'
                )
                ->addIndex(
                    $installer->getIdxName('simplegoogleshopping_functions', ['id']),
                    ['id']
                )
                ->setComment('Simple Google Shopping Custom Functions Table');

            $installer->getConnection()->createTable($simplegoogleshoppingFunctionsTable);

            $this->schemaSetup->getConnection()->endSetup();
        }
    }


    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
