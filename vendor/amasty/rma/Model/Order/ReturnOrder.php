<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Order;

use Amasty\Rma\Api\Data\ReturnOrderInterface;
use Magento\Framework\DataObject;

class ReturnOrder extends DataObject implements ReturnOrderInterface
{

    /**
     * @inheritdoc
     */
    public function setOrder($order)
    {
        return $this->setData(ReturnOrderInterface::ORDER, $order);
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->_getData(ReturnOrderInterface::ORDER);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->_getData(ReturnOrderInterface::ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        return $this->setData(ReturnOrderInterface::ITEMS, $items);
    }
}
