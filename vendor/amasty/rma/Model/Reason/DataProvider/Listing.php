<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Reason\DataProvider;

use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create()->addNotDeletedFilter();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
