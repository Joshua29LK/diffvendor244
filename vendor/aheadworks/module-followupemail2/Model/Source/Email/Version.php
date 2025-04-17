<?php
namespace Aheadworks\Followupemail2\Model\Source\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Version
 * @package Aheadworks\Followupemail2\Model\Source\Email
 */
class Version implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => EmailInterface::CONTENT_VERSION_A,
                'label' => __('Version A')
            ],
            [
                'value' => EmailInterface::CONTENT_VERSION_B,
                'label' => __('Version B')
            ],
        ];
    }
}
