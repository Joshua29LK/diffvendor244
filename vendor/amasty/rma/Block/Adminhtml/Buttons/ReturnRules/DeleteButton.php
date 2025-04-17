<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Adminhtml\Buttons\ReturnRules;

use Amasty\Rma\Block\Adminhtml\Buttons\GenericButton;
use Amasty\Rma\Controller\Adminhtml\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getRuleId()) {
            return [];
        }
        $alertMessage = __('Are you sure you want to do this?');
        $onClick = sprintf('deleteConfirm("%s", "%s")', $alertMessage, $this->getDeleteUrl());

        $data = [
            'label' => __('Delete'),
            'class' => 'delete',
            'id' => 'condition-edit-delete-button',
            'on_click' => $onClick,
            'sort_order' => 20,
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', [RegistryConstants::RULE_ID => $this->getRuleId()]);
    }

    /**
     * @return null|int
     */
    public function getRuleId()
    {
        return (int)$this->request->getParam(RegistryConstants::RULE_ID);
    }
}
