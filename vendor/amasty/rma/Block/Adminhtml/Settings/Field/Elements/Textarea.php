<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Adminhtml\Settings\Field\Elements;

class Textarea extends \Magento\Framework\View\Element\AbstractBlock
{
    public function toHtml()
    {
        return '<textarea id="' . $this->getInputId() .
            '"' .
            ' name="' .
            $this->getInputName() .
            '"><%- ' . $this->getColumnName() .' %></textarea>';
    }
}
