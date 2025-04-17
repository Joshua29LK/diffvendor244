<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BalanceChangeType implements OptionSourceInterface
{
    public const TYPE_ADDITION = 0;
    public const TYPE_SUBTRACTION = 1;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::TYPE_ADDITION => __('Balance Addition'),
            self::TYPE_SUBTRACTION => __('Balance Subtraction'),
        ];
    }
}
