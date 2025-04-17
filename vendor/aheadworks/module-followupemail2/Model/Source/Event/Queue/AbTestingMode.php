<?php
namespace Aheadworks\Followupemail2\Model\Source\Event\Queue;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AbTestingMode
 * @package Aheadworks\Followupemail2\Model\Source\Event
 */
class AbTestingMode implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ],
        ];
    }
}
