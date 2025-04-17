<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class Email
 * @package Aheadworks\Followupemail2\Model\Event\Plugin
 * @codeCoverageIgnore
 */
class Email
{
    /**
     * Session key to store guest email
     */
    const GUEST_EMAIL_KEY = 'AW_FUE2_GUEST_EMAIL';

    /**
     * @var EventHistoryManagementInterface
     */
    private $eventHistoryManagement;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param Config $config
     * @param CheckoutSession $session
     */
    public function __construct(
        EventHistoryManagementInterface $eventHistoryManagement,
        Config $config,
        CheckoutSession $session
    ) {
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->config = $config;
        $this->checkoutSession = $session;
    }

    /**
     * Store guest email
     *
     * @param \Magento\Customer\Api\AccountManagementInterface $interceptor
     * @param $customerEmail
     * @param $websiteId
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeIsEmailAvailable(
        \Magento\Customer\Api\AccountManagementInterface $interceptor,
        $customerEmail,
        $websiteId = null
    ) {
        $this->checkoutSession->setData(self::GUEST_EMAIL_KEY, $customerEmail);

        /** @var QuoteModel|null $quote */
        $quote = $this->checkoutSession->getQuote();
        if ($quote) {
            if ($this->config->isEnabled()
                && !$quote->getCustomerEmail()
                && $quote->getIsActive()
                && $quote->getItemsCount() > 0
            ) {
                $customerName = ($quote->getCustomerFirstname() && $quote->getCustomerLastname()) ?
                    $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname() :
                    $quote->getBillingAddress()->getFirstname() . ' ' . $quote->getBillingAddress()->getLastname();

                $cartData = array_merge($quote->getData(), [
                    'email' => $customerEmail,
                    'customer_name' => $customerName
                ]);
                $this->eventHistoryManagement->addEvent(EventInterface::TYPE_ABANDONED_CART, $cartData);
            }
        }

        return null;
    }
}
