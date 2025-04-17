<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Adminhtml\Buttons\Resolution;

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
        if (!$this->getResolutionId()) {
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
        return $this->getUrl('*/*/delete', [RegistryConstants::RESOLUTION_ID => $this->getResolutionId()]);
    }

    /**
     * @return null|int
     */
    public function getResolutionId()
    {
        return (int)$this->request->getParam(RegistryConstants::RESOLUTION_ID);
    }
}
