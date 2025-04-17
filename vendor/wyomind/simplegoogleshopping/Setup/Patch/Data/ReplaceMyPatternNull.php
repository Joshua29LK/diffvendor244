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

class ReplaceMyPatternNull implements DataPatchInterface, PatchRevertableInterface
{
    protected $coreDate;
    protected $feedsCollectionFactory;
    protected $state;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;


    const version = "11.0.1";
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

                foreach ($collection as $feed) {
                    $pattern = $feed->getSimplegoogleshoppingXmlitempattern();
                    $re = '/\$myPattern\s*=\s*null;/';
                    preg_match_all($re, $pattern, $matches);
                    foreach ($matches[0] as $match) {
                        $pattern = str_replace($match, '$this->skip();', (string)$pattern);
                    }
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
            Init::class,
            ReplaceCategoryIndex::class
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
