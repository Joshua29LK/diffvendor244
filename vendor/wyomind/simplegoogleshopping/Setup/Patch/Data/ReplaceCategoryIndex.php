<?php

namespace Wyomind\SimpleGoogleShopping\Setup\Patch\Data;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\Website;
use Wyomind\SimpleGoogleShopping\Model\ResourceModel\Feeds\CollectionFactory;
use Wyomind\SimpleGoogleShopping\Setup\Patch\Schema\Init as SchemaInit;

class ReplaceCategoryIndex implements DataPatchInterface, PatchRevertableInterface
{
    protected $feedsCollectionFactory;
    protected $state;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;


    const version = "11.0.0";
    private $dataVersion = 0;


    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $orderCollectionFactory
     * @param Website $websiteModel
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ModuleResource           $moduleResource,
        CollectionFactory        $feedsCollectionFactory,
        State                    $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataVersion = $moduleResource->getDataVersion("Wyomind_SimpleGoogleShopping");
        $this->feedsCollectionFactory = $feedsCollectionFactory;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {

        if (version_compare($this->dataVersion, self::version) >= 0) {
            try {
                $this->state->setAreaCode(Area::AREA_ADMINHTML);

                $this->moduleDataSetup->getConnection()->startSetup();

                $collection = $this->feedsCollectionFactory->create();

                $re = '/.categories([^|}]+)index="?\'?([0-9]+)"?\'?/';
                foreach ($collection as $feed) {
                    $pattern = str_replace(['"{{', '}}"', "'{{", "}}'", "php="], ['{{', '}}', "{{", "}}", "output="], (string)$feed->getSimplegoogleshoppingXmlitempattern());
                    $pattern = preg_replace($re, '.categories${1}nth="${2}"', $pattern);
                    $feed->setSimplegoogleshoppingXmlitempattern($pattern);
                    $feed->save();
                }

                $this->moduleDataSetup->getConnection()->endSetup();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            SchemaInit::class,
            Init::class
        ];
    }

    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
