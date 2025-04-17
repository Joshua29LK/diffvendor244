<?php
namespace Aheadworks\Followupemail2\Model\Source;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Variables
 * @package Aheadworks\Followupemail2\Model\Source
 */
class Variables implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $variables;

    /**
     * @param string $eventType
     */
    public function __construct($eventType)
    {
        $this->variables = [];
        $this->variables[] = ['value' => 'customer_name', 'label' => __('Customer Name')];
        if ($eventType !== EventInterface::TYPE_ORDER_STATUS_CHANGED) {
            $this->variables[] = ['value' => 'customer.firstname', 'label' => __('Customer First Name')];
            $this->variables[] = ['value' => 'customer.lastname', 'label' => __('Customer Last Name')];
        } else {
            $this->variables[] = ['value' => 'customer_firstname', 'label' => __('Customer First Name')];
        }
        $this->variables[] = ['value' => 'email', 'label' => __('Customer Email')];
        $this->variables[] = ['value' => 'store.name', 'label' => __('Store Name')];
        if ($eventType == EventInterface::TYPE_ABANDONED_CART) {
            $this->variables[] = ['value' => 'quote.subtotal|formatPrice', 'label' => __('Cart Subtotal')];
            $this->variables[] = ['value' => 'quote.grand_total|formatPrice', 'label' => __('Cart Grand Total')];
            $this->variables[] = ['value' => 'url_restore_cart', 'label' => __('Restore Cart Link')];
        }
        if ($eventType == EventInterface::TYPE_ORDER_STATUS_CHANGED) {
            $this->variables[] = ['value' => 'order.getIncrementId()', 'label' => __('Order Increment Id')];
            $this->variables[] = ['value' => 'order.status', 'label' => __('Order Status')];
            $this->variables[] = ['value' => 'order.subtotal|formatPrice', 'label' => __('Order Subtotal')];
            $this->variables[] = ['value' => 'order.grand_total|formatPrice', 'label' => __('Order Grand Total')];
        }
        $this->variables[] = [
            'value' => 'url_unsubscribe_all',
            'label' => __('Unsubscribe from all')
        ];
        $this->variables[] = [
            'value' => 'url_unsubscribe_event_type',
            'label' => __('Unsubscribe from event type')
        ];
        $this->variables[] = [
            'value' => 'url_unsubscribe_event',
            'label' => __('Unsubscribe from current event')
        ];
    }

    /**
     * Retrieve option array of follow up email variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        foreach ($this->variables as $variable) {
            $optionArray[] = [
                'value' => '{{var ' . $variable['value'] . '}}',
                'label' => $variable['label'],
            ];
        }
        if ($withGroup && $optionArray) {
            $optionArray = ['label' => __('Follow Up Email 2'), 'value' => $optionArray];
        }
        return $optionArray;
    }
}
