<?php
namespace Aheadworks\Followupemail2\Model\Source\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Prediction
 * @package Aheadworks\Followupemail2\Model\Source\Email
 */
class When implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => EmailInterface::WHEN_AFTER,
                'label' => __('After')
            ],
            [
                'value' => EmailInterface::WHEN_BEFORE,
                'label' => __('Before')
            ],
        ];
    }
}
