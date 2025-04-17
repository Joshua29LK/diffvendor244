<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Adminhtml\Buttons\Request;

use Amasty\Rma\Block\Adminhtml\Buttons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class HistoryButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            'label' => __('History'),
            'class' => 'action- scalable',
            'id' => 'amrma-history-button',
            'on_click' => 'require("uiRegistry").get("amrma_request_form.amrma_request_form.modal").toggleModal();'
        ];

        return $data;
    }
}
