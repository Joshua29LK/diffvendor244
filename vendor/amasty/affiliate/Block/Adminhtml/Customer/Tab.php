<?php

namespace Amasty\Affiliate\Block\Adminhtml\Customer;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Tab extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Affiliate Account');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Affiliate Account');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return $this->getUrl('amasty_affiliate/account_customer/edit', ['_current' => true]);
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}

