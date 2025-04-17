<?php
declare(strict_types = 1);

namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\BannerRepositoryInterface;
use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Api\LinksRepositoryInterface;
use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\AccountCookieManager;
use Amasty\Affiliate\Model\LinksFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class ActionPredispatchObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var LinksRepositoryInterface
     */
    private $linksRepository;

    /**
     * @var LinksFactory
     */
    private $linksFactory;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var AccountCookieManager
     */
    private $accountCookieManager;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AccountRepositoryInterface $accountRepository,
        LinksRepositoryInterface $linksRepository,
        BannerRepositoryInterface $bannerRepository,
        LinksFactory $linksFactory,
        AccountCookieManager $accountCookieManager,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->accountRepository = $accountRepository;
        $this->linksRepository = $linksRepository;
        $this->linksFactory = $linksFactory;
        $this->bannerRepository = $bannerRepository;
        $this->accountCookieManager = $accountCookieManager;
        $this->url = $url;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getRequest();
        $affiliateUlrParameter = $this->scopeConfig->getValue('amasty_affiliate/url/parameter');
        $accountCode = (string)$request->getParam($affiliateUlrParameter);
        if (!empty($accountCode)) {
            $account = $this->getAccountByReferringCode($accountCode);
            if (!$account) {
                $defaultNoRouteUrl = $this->scopeConfig->getValue(
                    'web/default/no_route',
                    ScopeInterface::SCOPE_STORE
                );
                $redirectUrl = $this->url->getUrl($defaultNoRouteUrl);
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($redirectUrl);

                return;
            }

            if ($account->getIsAffiliateActive()) {
                $this->accountCookieManager->addToCookies($account, $request);
                /** @var \Amasty\Affiliate\Model\Links $link */
                $link = $this->linksFactory->create();
                $data = [
                    'link_type' =>  $request->getParam('referring_service'),
                    'affiliate_account_id' => $account->getAccountId()
                ];
                if ($request->getParam('element_id')) {
                    $data['element_id'] = $request->getParam('element_id');
                }

                if ($data['link_type'] == \Amasty\Affiliate\Model\Links::TYPE_BANNER) {
                    /** @var \Amasty\Affiliate\Model\Banner $banner */
                    $banner = $this->bannerRepository->get($data['element_id']);
                    $banner->setClicks($banner->getClickCount($data['affiliate_account_id']));
                    $this->bannerRepository->save($banner);
                }

                $link->addData($data);
                $this->linksRepository->save($link);

                $url = $request->getUri()->getPath();
                $queryParams = $request->getQueryValue();
                $affiliateQueryParams = ['referring_service', 'element_id', $affiliateUlrParameter];

                foreach ($queryParams as $key => $value) {
                    if (in_array($key, $affiliateQueryParams)) {
                        unset($queryParams[$key]);
                    }
                }
                $url = $request->getUri()->getPath();

                if (count($queryParams)) {
                    $url = $url . '?' . http_build_query($queryParams);
                }

                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
            }
        }
    }

    /**
     * Retrieves account entity using referring code
     *
     * @param string $referringCode
     * @return AccountInterface|Account|null
     */
    private function getAccountByReferringCode(string $referringCode)
    {
        try {
            return $this->accountRepository->getByReferringCode($referringCode);
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
