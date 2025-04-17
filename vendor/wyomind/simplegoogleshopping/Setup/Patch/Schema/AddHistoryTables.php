<?php

namespace Wyomind\SimpleGoogleShopping\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Wyomind\Framework\Helper\History;

class AddHistoryTables implements SchemaPatchInterface, PatchRevertableInterface
{


    const version = "14.0.0";

    /**
     * @var SchemaSetupInterface
     */
    protected $schemaSetup;
    private $dbVersion = 0;

    /**
     * @var History
     */
    protected $historyHelper;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        ModuleResource       $moduleResource,
        History              $historyHelper
    ) {

        $this->schemaSetup = $schemaSetup;
        $this->dbVersion = $moduleResource->getDbVersion("Wyomind_SimpleGoogleShopping");
        $this->historyHelper = $historyHelper;
    }


    public function apply()
    {

        if (version_compare($this->dbVersion, self::version) >= 0) {
            $this->schemaSetup->getConnection()->startSetup();
            $installer = $this->schemaSetup;

            $tableName = 'simplegoogleshopping_feeds';

            if ($installer->tableExists($tableName)) {
                $connection = $installer->getConnection();
                $connection->addColumn(
                    $installer->getTable($tableName),
                    'simplegoogleshopping_name',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Data Feed Name'
                    ]
                );

                $connection->addColumn(
                    $installer->getTable($tableName),
                    'simplegoogleshopping_note',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Data Feed Note'
                    ]
                );
            }

            // Version history table - a line is added each time a feed is updated
            $this->historyHelper->createVersionHistoryTable($installer, $tableName);
            $this->historyHelper->createActionHistoryTable($installer, $tableName);

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
        return [
            Init::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
