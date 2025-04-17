<?php


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class RefferingCodesManagement
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var Http
     */
    private $request;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CookieMetadataFactory $cookieMetadataFactory,
        Http $request,
        CookieManagerInterface $cookieManager,
        CollectionFactory $collectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->request = $request;
        $this->cookieManager = $cookieManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Add current affiliate referring code to cookies
     */
    public function addToCookies()
    {
        $cookieExpiration = $this->scopeConfig
                ->getValue('amasty_affiliate/general/cookie_expiration') * 24 * 60 * 60;//in seconds
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($cookieExpiration)
            ->setPath('/')
            ->setSecure($this->request->isSecure());
        $this->cookieManager->setPublicCookie(
            \Amasty\Affiliate\Model\RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE,
            $this->getReferringCode(),
            $publicCookieMetadata
        );
    }

    /**
     * Generate referring code for affiliate account
     */
    public function generateReferringCode()
    {
        return $this->generateRandomString($this->getCodeLength());
    }

    /**
     * @param $length
     * @return string
     */
    public function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        //checking for unique code
        /** @var \Amasty\Affiliate\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('referring_code', ['eq' => $randomString]);
        if ($collection->getSize() > 0) {
            $randomString = $this->generateRandomString($length);
        }

        return $randomString;
    }

    /**
     * Get code length for affiliate url parameter
     * @return int|mixed
     */
    protected function getCodeLength()
    {
        $length = $this->scopeConfig->getValue('amasty_affiliate/url/length');

        if ($length < 4 || $length > 31) {
            $length = 10;
        }

        return $length;
    }
}
