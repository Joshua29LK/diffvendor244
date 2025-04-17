<?php

namespace Amasty\Affiliate\Ui\Component\Listing\Columns;

class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const NAME = 'column.name';

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = $item['firstname'] .  ' ' . $item['lastname'];
            }
        }

        return $dataSource;
    }
}
