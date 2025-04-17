<?php

namespace Amasty\Affiliate\Block\Account;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\CouponListProvider;
use Amasty\Affiliate\Model\ResourceModel\Banner\Collection;
use Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Element\Template;

class Promo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var string
     */
    protected $_template = 'account/promo.phtml';

    /**
     * @var CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var Collection
     */
    private $bannerCollection;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var Data
     */
    private $urlHelper;
    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        AccountRepositoryInterface $accountRepository,
        CollectionFactory $couponCollectionFactory,
        Collection $bannerCollection,
        Account $account,
        Data $ulrHelper,
        CouponListProvider $couponListProvider,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->accountRepository = $accountRepository;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->bannerCollection = $bannerCollection;
        $this->account = $account;
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlHelper = $ulrHelper;
        $this->couponListProvider = $couponListProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Affiliate Programs'));
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @return \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection|bool
     */
    public function getCoupons()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        $this->customerId = $customerId;

        $account = $this->accountRepository->getByCustomerId($customerId);

        return $this->couponListProvider->getCollectionForFrontend(
            $account->getAccountId(),
            $customerId,
            $this->customerSession,
            true
        );
    }

    /**
     * @return Collection
     */
    public function getBanners()
    {
        return $this->bannerCollection->addStatusFilter();
    }

    /**
     * @return string
     */
    public function getLinkParams()
    {
        $codeKey = $this->_scopeConfig->getValue('amasty_affiliate/url/parameter');
        /** @var Account $account */
        $account = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
        $code = $account->getReferringCode();

        $linkParams = $codeKey . '='. $code . '&referring_service=' . \Amasty\Affiliate\Model\Links::TYPE_LINK;

        return $linkParams;
    }

    /**
     * @param \Amasty\Affiliate\Model\Banner $banner
     * @return string
     */
    public function getRelNofollow($banner)
    {
        $relNofollow = '';
        if ($banner->getRelNoFollow()) {
            $relNofollow = "rel='nofollow'";
        }

        return $relNofollow;
    }

    /**
     * @param \Amasty\Affiliate\Model\Banner $banner
     * @return string
     */
    public function getBannerLink($banner)
    {
        $accountCode = $this->accountRepository
            ->getByCustomerId($this->customerSession->getCustomerId())
            ->getReferringCode();
        $codeParameter = $this->scopeConfig->getValue('amasty_affiliate/url/parameter');

        $params = [
            $codeParameter => $accountCode,
            'referring_service' => 'banner',
            'element_id' => $banner->getBannerId()
        ];

        $bannerLink = $this->urlHelper->addRequestParam($banner->getLink(), $params);

        return $bannerLink;
    }

    /**
     * @param \Amasty\Affiliate\Api\Data\CouponInterface $coupon
     * @return \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection
     */
    public function getCustomCoupons($coupon)
    {
        return $this->couponListProvider->getCollectionForFrontend(
            $coupon->getAccountId(),
            $this->customerId,
            $this->customerSession,
            0,
            $coupon->getProgramId()
        );
    }
}
