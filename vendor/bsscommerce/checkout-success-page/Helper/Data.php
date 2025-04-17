<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->countryFactory = $countryFactory;
        $this->filterProvider = $filterProvider;
        $this->httpContext = $httpContext;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $path
     * @return bool
     */
    public function isConfigEnable($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
    public function getEditor($content)
    {
        $html = $this->filterProvider->getBlockFilter()->filter($content);
        return $html;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            return $this->customerSession->getCustomer()->getGroupId();
        }
        return 0;
    }

    /**
     * @param $params
     * @return bool|string
     */
    public function serializer($params)
    {
        return $this->serializer->serialize($params);
    }

    /**
     * @param $param
     * @return bool|string
     */
    public function getParams($param)
    {
        $params = base64_decode($param);
        try {
            $post = $this->serializer->unserialize($params);
            if (isset($post['oar_order_id']) && isset($post['oar_billing_lastname']) && isset($post['oar_email'])) {
                return $post;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}