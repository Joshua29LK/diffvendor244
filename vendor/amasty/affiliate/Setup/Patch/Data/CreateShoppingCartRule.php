<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\SalesRule\Api\Data\RuleInterface;

class CreateShoppingCartRule implements DataPatchInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var GroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var WebsiteCollectionFactory
     */
    private $websiteCollectionFactory;

    /**
     * @var RuleInterfaceFactory
     */
    private $ruleFactory;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        State $appState,
        RuleInterfaceFactory $ruleFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        GroupCollectionFactory $customerGroupCollectionFactory,
        RuleRepositoryInterface $ruleRepository,
        ResourceInterface $moduleResource
    ) {
        $this->appState = $appState;
        $this->ruleFactory = $ruleFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->ruleRepository = $ruleRepository;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): void
    {
        $setupVersion = $this->moduleResource->getDbVersion('Amasty_Affiliate');

        if (!$setupVersion) {
            $this->appState->emulateAreaCode('adminhtml', [$this, 'createShoppingCartRule']);
        }
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function createShoppingCartRule(): void
    {
        $data = [
            'name' => 'Default Affilate Program Rule',
            'is_active' => '0',
            'simple_action' => 'by_fixed',
            'discount_amount' => '5.0000',
            'coupon_type' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'use_auto_generation' => '1'
        ];
        /** @var Rule $rule */
        $rule = $this->ruleFactory->create(['data' => $data]);
        $websites = $this->websiteCollectionFactory->create()->toOptionHash();
        $websiteIds = array_keys($websites);
        $rule->setWebsiteIds($websiteIds);
        $customerGroups = $this->customerGroupCollectionFactory->create()->toOptionHash();
        $customerGroupIds = array_keys($customerGroups);
        $rule->setCustomerGroupIds($customerGroupIds);
        $rule->setStopRulesProcessing(false);
        $this->ruleRepository->save($rule);
    }
}
