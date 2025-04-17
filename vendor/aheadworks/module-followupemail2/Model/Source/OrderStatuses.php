<?php
namespace Aheadworks\Followupemail2\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order;

/**
 * Class OrderStatuses
 * @package Aheadworks\Followupemail2\Model\Source
 */
class OrderStatuses implements OptionSourceInterface
{
    /**
     * @var string[]
     */
    private $stateStatuses = [
        Order::STATE_NEW,
        Order::STATE_PENDING_PAYMENT,
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_CANCELED,
        Order::STATE_HOLDED,
        Order::STATE_PAYMENT_REVIEW,
    ];

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @param OrderConfig $orderConfig
     */
    public function __construct(
        OrderConfig $orderConfig
    ) {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Get options
     *
     * @return []
     */
    public function toOptionArray()
    {
        $statuses = $this->stateStatuses
            ? $this->orderConfig->getStateStatuses($this->stateStatuses)
            : $this->orderConfig->getStatuses();

        foreach ($statuses as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label
            ];
        }
        return $options;
    }
}
