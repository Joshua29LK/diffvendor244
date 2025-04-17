<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\AccountInterfaceFactory;
use Amasty\Affiliate\Model\Repository\AccountRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AccountCreator
{

    /**
     * @var AccountInterfaceFactory
     */
    private $accountFactory;
    /**
     * @var RefferingCodesManagement
     */
    private $refferingCodesManagement;
    /**
     * @var Repository\AccountRepository
     */
    private $accountRepository;
    /**
     * @var NotificationSender
     */
    private $notificationSender;
    /**
     * @var CouponCreator
     */
    private $couponCreator;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        AccountInterfaceFactory $accountFactory,
        RefferingCodesManagement $refferingCodesManagement,
        AccountRepository $accountRepository,
        NotificationSender $notificationSender,
        CouponCreator $couponCreator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->accountFactory = $accountFactory;
        $this->refferingCodesManagement = $refferingCodesManagement;
        $this->accountRepository = $accountRepository;
        $this->notificationSender = $notificationSender;
        $this->couponCreator = $couponCreator;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $customerId
     * @param array $data
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function createAccount($customerId, $data)
    {
        $account = $this->accountFactory->create();
        $account->addData($data);
        $account->setCustomerId($customerId);
        $account->setAcceptedTermsConditions(true);
        $account->setReferringCode($this->refferingCodesManagement->generateReferringCode());
        $this->accountRepository->save($account);

        $this->couponCreator->addCoupon($account->getAccountId());
        if ($this->scopeConfig->getValue('amasty_affiliate/email/admin/new_affiliate')) {
            $this->notificationSender->sendAdminNotification($account);
        }
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/welcome')) {
            $this->notificationSender->sendAffiliateNotification($account);
        }

        return $account;
    }
}
