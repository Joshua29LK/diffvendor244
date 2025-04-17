<?php
namespace Aheadworks\Followupemail2\Model\Source\Email;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Minutes
 * @package Aheadworks\Followupemail2\Model\Source\Email
 */
class Minutes implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $units = __('minutes');
            for ($minutes = 0; $minutes < 60; $minutes += 5) {
                $this->options[] = [
                    'value' => $minutes,
                    'label' => $minutes . ' ' . $units
                ];
            }
        }
        return $this->options;
    }
}
