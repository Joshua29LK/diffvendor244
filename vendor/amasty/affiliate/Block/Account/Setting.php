<?php

namespace Amasty\Affiliate\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class Setting extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/setting.phtml';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Setting constructor.
     * @param Template\Context $context
     * @param \Amasty\Affiliate\Model\Account $account
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Session $customerSession,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->account = $account;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Affiliate Settings'));
    }

    /**
     * @return string
     */
    public function checkSubscribe()
    {
        $checked = '';

        if ($this->getAccount()->getAccountId()) {
            if ($this->getAccount()->getReceiveNotifications()) {
                $checked = 'checked';
            }
        } elseif ($this->scopeConfig->getValue('amasty_affiliate/account/email_notifications')) {
            $checked = 'checked';
        }

        return $checked;
    }

    /**
     * @return string
     */
    public function checkConditions()
    {
        $checked = '';

        if ($this->getAccount()->getAccountId()) {
            if ($this->getAccount()->getAcceptedTermsConditions()) {
                $checked = 'checked';
            }
        } elseif ($this->scopeConfig->getValue('amasty_affiliate/terms/checkbox_checked')) {
            $checked = 'checked';
        }

        return $checked;
    }

    /**
     * @return mixed
     */
    public function getConditionsCheckboxText()
    {
        return $this->scopeConfig->getValue('amasty_affiliate/terms/checkbox_text');
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function getAccount()
    {
        return $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
    }

    /**
     * @return string
     */
    public function getTermsUrl()
    {
        $url = $this->urlBuilder->getUrl('amasty-affiliate-conditions');

        return $url;
    }

    /**
     * @return string
     */
    public function disabledConditions()
    {
        $disabled = '';

        if ($this->getAccount() != null && $this->getAccount()->getAccountId() != null) {
            $disabled = 'disabled';
        }

        return $disabled;
    }
}
