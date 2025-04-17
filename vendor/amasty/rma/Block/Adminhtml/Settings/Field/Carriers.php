<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Adminhtml\Settings\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Carriers extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn(
            'carrier_code',
            ['label' => __('Carrier Code'), 'class' => 'required-entry']
        );
        $this->addColumn(
            'carrier_label',
            ['label' => __('Carrier Label'), 'class' => 'required-entry']
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
