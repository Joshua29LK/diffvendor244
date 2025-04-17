<?php

namespace Amasty\Affiliate\Ui\Component\Listing\Columns;

use Amasty\Affiliate\Model\Transaction;

class TransactionMessage extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['message'] = __('Per Profit');
                if ($item['type'] == Transaction::TYPE_PER_SALE) {
                    $item['message'] = __('Commission for order #') . $item['increment_id'];
                } elseif ($item['type'] == Transaction::TYPE_WITHDRAWAL) {
                    $item['message'] = 'Withdrawal';
                }
            }
        }

        return $dataSource;
    }

    protected function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'sales_order.increment_id',
                strtoupper($sorting['direction'])
            );
        }
    }
}
