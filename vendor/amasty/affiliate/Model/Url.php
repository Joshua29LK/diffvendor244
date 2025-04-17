<?php

namespace Amasty\Affiliate\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Amasty\Affiliate\Controller\Router;
use \Magento\Framework\UrlInterface;

class Url
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private $baseUrl;
    /**
     * @var UrlInterface
     */
    private $url;

    function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }

    public function getUrlPrefix()
    {
        if ($this->baseUrl === null) {
            if ($url = $this->scopeConfig->getValue(Router::AMASTY_AFFILIATE_URL_GENERAL_URL)) {
                $this->baseUrl = trim($url, '/ ');
            } else {
                $this->baseUrl = Router::AMASTY_AFFILIATE_URL_STANDARD_PREFIX;
            }
        }

        return $this->baseUrl;
    }

    public function getPath($path)
    {
        return $this->getUrlPrefix() . '/' . ltrim($path, '/');
    }

    public function getUrl($path, $routeParams = null)
    {
        return $this->url->getUrl($path, $routeParams);
    }
}
