<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Program\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\Rule;

class Rules extends AbstractOptions
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->coreRegistry = $coreRegistry;
    }

    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();

        if (count($options) <= 0) {
            $this->coreRegistry->register('affiliate_rules_are_empty', true, true);
        }

        return $options;
    }

    public function toArray(): array
    {
        $ruleCollection = $this->ruleCollectionFactory->create();
        $ruleCollection->addFieldToFilter(
            'coupon_type',
            ['eq' => Rule::COUPON_TYPE_SPECIFIC]
        )->addFieldToFilter('use_auto_generation', ['eq' => 1]);

        $options = [];
        foreach ($ruleCollection as $rule) {
            $options[$rule->getRuleId()] = $rule->getName();
        }

        return $options;
    }
}
