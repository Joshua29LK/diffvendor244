<?php
namespace Aheadworks\Followupemail2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class Convert serialized data to json format
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Serializer $serializer
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Serializer $serializer
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->serializer = $serializer;
    }

    /**
     * Apply data patch
     */
    public function apply()
    {
        $this->convertSerializedDataToJson($this->moduleDataSetup);
    }

    /**
     * Convert metadata from serialized to JSON format if needed
     *
     * @param ModuleDataSetupInterface $setup
     * @return ConvertSerializedDataToJson
     */
    public function convertSerializedDataToJson($setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_fue2_event');

        $select = $connection->select()->from($tableName);
        $events = $connection->fetchAssoc($select);

        foreach ($events as $event) {
            $toUpdate = [];
            if (!isset($event['cart_conditions']) || !isset($event['lifetime_conditions'])) {
                continue;
            }
            $cartCondUnserialized = $this->unserializeString($event['cart_conditions']);
            if ($cartCondUnserialized !== false && \is_array($cartCondUnserialized)) {
                $toUpdate['cart_conditions'] = $this->serializer->serialize($cartCondUnserialized);
            }

            $lifetimeCondUnserialized = $this->unserializeString($event['lifetime_conditions']);
            if ($lifetimeCondUnserialized !== false && \is_array($lifetimeCondUnserialized)) {
                $toUpdate['lifetime_conditions'] = $this->serializer->serialize($lifetimeCondUnserialized);
            }

            if (!empty($toUpdate)) {
                $connection->update($tableName, $toUpdate, ['id = ?' => $event['id']]);
            }
        }

        return $this;
    }

    /**
     * Unserialize string with unserialize method
     *
     * @param string $string
     * @return array|bool
     */
    private function unserializeString($string)
    {
        $result = $this->serializer->unserialize($string);

        if ($result !== false || $string === 'b:0;') {
            return $result;
        }

        return false;
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
        return '2.0.2';
    }
}
