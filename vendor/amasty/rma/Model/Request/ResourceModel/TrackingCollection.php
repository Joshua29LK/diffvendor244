<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Request\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class TrackingCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Request\Tracking::class,
            \Amasty\Rma\Model\Request\ResourceModel\Tracking::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
