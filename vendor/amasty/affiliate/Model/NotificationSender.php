<?php

namespace Amasty\Affiliate\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

class NotificationSender
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Mailsender
     */
    private $mailsender;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Mailsender $mailsender,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mailsender = $mailsender;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param \Amasty\Affiliate\Api\Data\AccountInterface $account
     * @param Int $status
     */
    public function sendAffiliateStatusEmail($account, $status)
    {
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/account_status')
            && $account->getReceiveNotifications()
        ) {
            $emailData = $account->getData();
            $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();
            $emailData['status'] = $status == 1 ? __('Active') : __('Inactive');
            $customer = $this->customerRepository->getById($account->getCustomerId());
            $sendToMail = $customer->getEmail();
            $this->mailsender->sendAffiliateMail($emailData, Mailsender::TYPE_AFFILIATE_STATUS, $sendToMail, $account);
        }
    }

    /**
     * Send email notification to admin about new affiliate account
     *
     * @param $accountId
     */
    public function sendAdminNotification($account)
    {
        $customer = $this->customerRepository->getById($account->getCustomerId());
        $emailData = $account->getData();
        $emailData['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        $emailData['email'] = $customer->getEmail();
        $sendToMail = $this->scopeConfig->getValue('amasty_affiliate/email/general/recipient_email');

        $this->mailsender->sendMail($emailData, Mailsender::TYPE_ADMIN_NEW_ACCOUNT, $sendToMail);
    }

    /**
     * Send email notification to affiliate about creating of account
     *
     * @param $accountId
     */
    public function sendAffiliateNotification($account)
    {
        $emailData = $account->getData();
        $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();
        $customer = $this->customerRepository->getById($account->getCustomerId());
        $sendToMail = $customer->getEmail();

        $this->mailsender->sendAffiliateMail($emailData, Mailsender::TYPE_AFFILIATE_WELCOME, $sendToMail, $account);
    }
}
