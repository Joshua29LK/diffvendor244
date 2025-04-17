<?php

namespace Amasty\Affiliate\Block\Adminhtml\Program\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * SaveButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->messageManager = $messageManager;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->coreRegistry->registry('affiliate_rules_are_empty')) {
            $messageText = __('Please create shopping cart price rule with \'Auto generated coupons\'');
            $this->messageManager->addErrorMessage($messageText);
        }
        if ($this->canRender('save')) {
            $data = [
                'label' => __('Save'),
                'class' => 'save primary',
                'on_click' => '',
            ];
        }
        return $data;
    }
}
