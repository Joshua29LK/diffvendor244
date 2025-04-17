<?php

namespace Amasty\Affiliate\Controller;

use Magento\Framework\Module\Manager;
use Magento\Framework\App\Action\Forward;

class Router implements \Magento\Framework\App\RouterInterface
{
    public const AMASTY_AFFILIATE_URL_GENERAL_URL = 'amasty_affiliate/url/general_url';

    public const AMASTY_AFFILIATE_URL_STANDARD_PREFIX = 'amasty_affiliate';

    /** @var \Magento\Framework\App\ActionFactory */
    protected $actionFactory;

    /** @var \Magento\Framework\App\ResponseInterface */
    protected $_response;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var  Manager */
    protected $moduleManager;

    /**
     * Router constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Manager $moduleManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|void
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = explode(DIRECTORY_SEPARATOR, trim($request->getPathInfo(), DIRECTORY_SEPARATOR));
        $compareUrl = $this->getPathUrlFromSetting();

        if (array_key_exists(0, $identifier) && ($compareUrl == $identifier[0])) {
            $newPathInfo = str_replace(
                $compareUrl,
                self::AMASTY_AFFILIATE_URL_STANDARD_PREFIX,
                $request->getPathInfo()
            );
            $request->setPathInfo($newPathInfo);

            return $this->actionFactory->create(Forward::class, ['request' => $request]);
        }
    }

    /**
     * @return string
     */
    protected function getPathUrlFromSetting()
    {
        return trim($this->scopeConfig->getValue(self::AMASTY_AFFILIATE_URL_GENERAL_URL), DIRECTORY_SEPARATOR);
    }
}
