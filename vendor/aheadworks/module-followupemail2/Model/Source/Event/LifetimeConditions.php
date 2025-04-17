<?php
namespace Aheadworks\Followupemail2\Model\Source\Event;

use Aheadworks\Followupemail2\Model\Event\LifetimeCondition;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LifetimeConditions
 * @package Aheadworks\Followupemail2\Model\Source\Event
 */
class LifetimeConditions implements OptionSourceInterface
{
    /**
     * @var LifetimeCondition
     */
    private $lifetimeCondition;

    /**
     * @param LifetimeCondition $lifetimeCondition
     */
    public function __construct(
        LifetimeCondition $lifetimeCondition
    ) {
        $this->lifetimeCondition = $lifetimeCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        $conditionOptions = $this->lifetimeCondition->getDefaultOptions();

        foreach ($conditionOptions as $optionValue => $optionLabel) {
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel
            ];
        }

        return $options;
    }
}
