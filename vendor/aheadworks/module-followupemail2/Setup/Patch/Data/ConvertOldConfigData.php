<?php
namespace Aheadworks\Followupemail2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class Convert old data config
 */
class ConvertOldConfigData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply data patch
     */
    public function apply()
    {
        $this->convertOldConfigData($this->moduleDataSetup);
    }

    /**
     * Convert old config data if needed
     *
     * @param ModuleDataSetupInterface $setup
     * @return ConvertOldConfigData
     */
    public function convertOldConfigData($setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('core_config_data');

        $select = $connection->select()
            ->from($tableName)
            ->where('path like "followupemail2/%"');
        $values = $connection->fetchAssoc($select);

        foreach ($values as $value) {
            $newPath = str_replace('followupemail2/', 'followupemailtwo/', $value['path']);
            $connection->update($tableName, ['path' => $newPath], ['config_id = ?' => $value['config_id']]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
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
    public static function getVersion()
    {
        return '2.1.0';
    }
}
