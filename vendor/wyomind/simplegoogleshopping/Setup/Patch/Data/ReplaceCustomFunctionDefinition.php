<?php

namespace Wyomind\SimpleGoogleShopping\Setup\Patch\Data;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\Website;
use Wyomind\SimpleGoogleShopping\Model\ResourceModel\Functions\CollectionFactory as CustomFunctionCollectionFactory;
use Wyomind\SimpleGoogleShopping\Setup\Patch\Schema\Init as SchemaInit;

class ReplaceCustomFunctionDefinition implements DataPatchInterface, PatchRevertableInterface
{
    protected $coreDate;
    protected $functionCollectionFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;


    const version = "13.1.2";

    private $dataVersion = 0;


    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $orderCollectionFactory
     * @param Website $websiteModel
     */
    public function __construct(
        ModuleDataSetupInterface        $moduleDataSetup,
        ModuleResource                  $moduleResource,
        CustomFunctionCollectionFactory $functionCollectionFactory,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataVersion = $moduleResource->getDataVersion("Wyomind_SimpleGoogleShopping");
        $this->functionCollectionFactory = $functionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {

        if (version_compare($this->dataVersion, self::version) >= 0) {
            $this->moduleDataSetup->getConnection()->startSetup();


            $functionCollection = $this->functionCollectionFactory->create();

            foreach ($functionCollection as $function) {
                $searchQuery = "<?php if (!function_exists(";
                if (substr($function->getScript(), 0, strlen($searchQuery)) !== $searchQuery) {
                    $function->setScript(preg_replace("/<\?php\sfunction\s([a-zA-z0-9]+)/", '<?php if (!function_exists("\1")) { function \1', $function->getScript()));
                    $function->setScript(str_replace("?>", "}\n?>", (string)$function->getScript()));
                    $function->save();
                }
            }

            $this->moduleDataSetup->getConnection()->endSetup();
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
            ReplaceCustomFunctionName::class
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
