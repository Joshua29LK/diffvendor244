<?php

namespace Amasty\Affiliate\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class AccountCookieManager
{
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        ScopeConfigInterface $scopeConfig,
        CookieManagerInterface $cookieManager
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Add current affiliate referring code to cookies
     *
     * @param \Amasty\Affiliate\Model\Account $account
     * @param $request
     */
    public function addToCookies($account, $request)
    {
        $cookieExpiration = $this->scopeConfig
                ->getValue('amasty_affiliate/general/cookie_expiration') * 24 * 60 * 60;//in seconds
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($cookieExpiration)
            ->setPath('/')
            ->setSecure($request->isSecure());
        $this->cookieManager->setPublicCookie(
            \Amasty\Affiliate\Model\RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE,
            $account->getReferringCode(),
            $publicCookieMetadata
        );
    }
}
