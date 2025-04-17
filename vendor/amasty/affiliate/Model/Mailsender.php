<?php

namespace Amasty\Affiliate\Model;

class Mailsender
{
    public const TYPE_ADMIN_NEW_WITHDRAWAL = 'admin_new_withdrawal';
    public const TYPE_ADMIN_NEW_ACCOUNT = 'admin_new_account';

    public const TYPE_AFFILIATE_WITHDRAWAL_STATUS = 'withdrawal_changed';
    public const TYPE_AFFILIATE_TRANSACTION_STATUS = 'transaction_changed';
    public const TYPE_AFFILIATE_TRANSACTION_NEW = 'transaction_created';
    public const TYPE_AFFILIATE_STATUS = 'affiliate_status';
    public const TYPE_AFFILIATE_WELCOME = 'affiliate_welcome';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $parseDataVars;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Mailsender constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\DataObject $parseDataVars
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObject $parseDataVars,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->parseDataVars = $parseDataVars;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $data
     * @param $type
     * @param $mail
     */
    public function sendMail($data, $type, $mail)
    {
        switch ($type) {
            case self::TYPE_ADMIN_NEW_WITHDRAWAL:
                $template = $this->scopeConfig->getValue('amasty_affiliate/email/admin/withdrawal_request_template');
                break;
            case self::TYPE_ADMIN_NEW_ACCOUNT:
                $template = $this->scopeConfig->getValue('amasty_affiliate/email/admin/new_affiliate_template');
                break;
            case self::TYPE_AFFILIATE_WITHDRAWAL_STATUS:
                $template = $this->scopeConfig->getValue(
                    'amasty_affiliate/email/affiliate/withdrawal_changed_template'
                );
                break;
            case self::TYPE_AFFILIATE_TRANSACTION_STATUS:
                $template = $this->scopeConfig->getValue(
                    'amasty_affiliate/email/affiliate/transaction_changed_template'
                );
                break;
            case self::TYPE_AFFILIATE_TRANSACTION_NEW:
                $template = $this->scopeConfig->getValue(
                    'amasty_affiliate/email/affiliate/transaction_created_template'
                );
                break;
            case self::TYPE_AFFILIATE_STATUS:
                $template = $this->scopeConfig->getValue(
                    'amasty_affiliate/email/affiliate/account_status_template'
                );
                break;
            case self::TYPE_AFFILIATE_WELCOME:
                $template = $this->scopeConfig->getValue(
                    'amasty_affiliate/email/affiliate/welcome_template'
                );
                break;
        }

        $this->parseDataVars->setData($data);

        if (isset($template)) {
            $sender = $this->scopeConfig->getValue('amasty_affiliate/email/general/sender_email_identity');

            $transport = $this->transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        'store' => $this->getStoreId(),
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND
                    ]
                )
                ->setTemplateVars(['data' => $this->parseDataVars])
                ->setFrom($sender)
                ->addTo($mail, $mail)
                ->getTransport();

            $transport->sendMessage();

        }
    }

    /**
     * @param $data
     * @param $type
     * @param $mail
     * @param \Amasty\Affiliate\Model\Account $account
     */
    public function sendAffiliateMail($data, $type, $mail, $account)
    {
        if ($account->getReceiveNotifications()) {
            $this->sendMail($data, $type, $mail);
        }
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
