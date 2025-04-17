<?php

namespace Amasty\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractTab extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    private $url;
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\Url $url,
        Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountRepository = $accountRepository;
        $this->url = $url;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    protected function authenticate()
    {
        $successAuthentication = false;

        $currentAccount = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
        if ($currentAccount->getAccountId() && $currentAccount->getIsAffiliateActive()) {
            $successAuthentication = true;
        }

        return $successAuthentication;
    }

    protected function _redirect($path, $arguments = [])
    {
        if (0 === strpos($path, '*/')) {
            $path = $this->url->getUrlPrefix() . substr($path, 1);
        }

        return parent::_redirect($path, $arguments);
    }
}
