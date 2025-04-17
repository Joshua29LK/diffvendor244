<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Program\ProgramCommissionCalculation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Affiliate\Model\ProgramCommissionCalculation::class,
            \Amasty\Affiliate\Model\ResourceModel\ProgramCommissionCalculation::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Serializable fields load fix
     *
     * @return Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->getItems() as $item) {
            $this->getResource()->unserializeFields($item);
            $item->setDataChanges(false);
        }
        return $this;
    }
}
