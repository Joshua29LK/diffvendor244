<?php
namespace Aheadworks\Followupemail2\Model\Source\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Followupemail2\Model\Source\Email
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
                'value' => EmailInterface::STATUS_DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => EmailInterface::STATUS_ENABLED,
                'label' => __('Enabled')
            ],
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $optionsArray = $this->toOptionArray();
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
