<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block;

class FooterLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Rma\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Rma\Model\ConfigProvider $configProvider,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (!$this->getData('path')) {
            $this->setData('path', $this->configProvider->getUrlPrefix() . '/requests');
        }

        return $this->getData('path');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Return Requests');
    }
}
