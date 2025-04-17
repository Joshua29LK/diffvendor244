<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Program\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractOptions implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->toArray() as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }

    abstract public function toArray(): array;
}
