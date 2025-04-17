<?php
namespace Aheadworks\Followupemail2\Model\Source\Queue;

use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Followupemail2\Model\Source\Queue
 */
class Status implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => QueueInterface::STATUS_PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => QueueInterface::STATUS_SENT,
                'label' => __('Sent')
            ],
            [
                'value' => QueueInterface::STATUS_FAILED,
                'label' => __('Failed')
            ],
            [
                'value' => QueueInterface::STATUS_CANCELLED,
                'label' => __('Cancelled')
            ],
        ];
    }
}
