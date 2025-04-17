<?php
namespace Aheadworks\Followupemail2\Model\Source\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class FailedEmailsMode
 * @package Aheadworks\Followupemail2\Model\Source\Event
 */
class FailedEmailsMode implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => EventInterface::FAILED_EMAILS_SKIP,
                'label' => __('Skip the failed email and continue sending out consecutive emails')
            ],
            [
                'value' => EventInterface::FAILED_EMAILS_CANCEL,
                'label' => __('Cancel all consecutive emails in the chain')
            ],
        ];
    }
}
