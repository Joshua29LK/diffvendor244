<?php
namespace Aheadworks\Followupemail2\Model\Source\Email;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Hours
 * @package Aheadworks\Followupemail2\Model\Source\Email
 */
class Hours implements OptionSourceInterface
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
            $units = __('hours');
            $unitsSingle = __('hour');
            for ($hour = 0; $hour < 24; $hour++) {
                $this->options[] = [
                    'value' => $hour,
                    'label' => $hour . ' ' . ($this->useSingleUnit($hour) ? $unitsSingle : $units)
                ];
            }
        }
        return $this->options;
    }

    /**
     * Use single unit
     *
     * @param int $value
     * @return bool
     */
    private function useSingleUnit($value)
    {
        return $value == 1;
    }
}
