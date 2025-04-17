<?php

namespace Amasty\Affiliate\Block\SocialButtons;

use Magento\Framework\View\Element\Template;

abstract class AbstractButtons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    protected $account;

    /**
     * AbstractButtons constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Model\Account $account
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\Account $account,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->accountRepository = $accountRepository;
        $this->account = $account;
        parent::__construct($context, $data);
    }

    public function showLinks()
    {
        $showLinks = false;

        if ($this->showConfig() && $this->accountRepository->isAffiliate()) {
            $showLinks = true;
        }

        return $showLinks;
    }

    public function showConfig() {}

    public function getSiteUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getReferringCode()
    {
        $account = $this->getAccount();

        return $account->getReferringCode();
    }

    public function getUrlAccountParameter()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/url/parameter');
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function getAccount()
    {
        $customerId = $this->customerSession->getCustomerId();
        $account = $this->account;
        if ($this->accountRepository->isAffiliate($customerId)) {
            $account = $this->accountRepository->getByCustomerId($customerId);
        }

        return $account;
    }

    public function getProfileId()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/account_id');
    }
}
