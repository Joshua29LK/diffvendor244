<?php

namespace Amasty\Affiliate\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Transaction
     */
    private $transaction;

    /**
     * IsActive constructor.
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     */
    public function __construct(\Amasty\Affiliate\Model\Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $availableOptions = $this->transaction->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
