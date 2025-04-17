<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Block\Guest;

class Login extends \Magento\Sales\Block\Widget\Guest\Form
{
    /**
     * @var \Amasty\Rma\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Amasty\Rma\Model\ConfigProvider $configProvider,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl(
            $this->configProvider->getUrlPrefix() . '/guest/loginPost',
            ['_secure' => true]
        );
    }
}
