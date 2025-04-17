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
use Wyomind\SimpleGoogleShopping\Model\ResourceModel\Functions\CollectionFactory as CustomFunctionCollectionFactory;
use Wyomind\SimpleGoogleShopping\Setup\Patch\Schema\Init as SchemaInit;

class ReplaceCustomFunctionName implements DataPatchInterface, PatchRevertableInterface
{
    protected $coreDate;
    protected $feedsCollectionFactory;
    protected $functionCollectionFactory;
    protected $state;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;


    const version = "13.1.0";
    private $dataVersion = 0;


    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $orderCollectionFactory
     * @param Website $websiteModel
     */
    public function __construct(
        ModuleDataSetupInterface        $moduleDataSetup,
        ModuleResource                  $moduleResource,
        CollectionFactory               $feedsCollectionFactory,
        CustomFunctionCollectionFactory $functionCollectionFactory,
        State                           $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataVersion = $moduleResource->getDataVersion("Wyomind_SimpleGoogleShopping");
        $this->feedsCollectionFactory = $feedsCollectionFactory;
        $this->functionCollectionFactory = $functionCollectionFactory;
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


                $toReplace = ["sgs_strtoupper", "sgs_strtolower", "sgs_implode", "sgs_html_entity_decode", "sgs_strip_tags", "sgs_htmlentities", "sgs_substr"];
                $replacement = ["wyomind_strtoupper", "wyomind_strtolower", "wyomind_implode", "wyomind_html_entity_decode", "wyomind_strip_tags", "wyomind_htmlentities", "wyomind_substr"];

                $functionCollection = $this->functionCollectionFactory->create();

                foreach ($functionCollection as $function) {
                    $function->setScript(str_replace($toReplace, $replacement, (string)$function->getScript()));
                    $function->save();
                }

                $collection = $this->feedsCollectionFactory->create();
                foreach ($collection as $feed) {
                    $pattern = $feed->getSimplegoogleshoppingXmlitempattern();
                    $pattern = str_replace($toReplace, $replacement, (string)$pattern);
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
