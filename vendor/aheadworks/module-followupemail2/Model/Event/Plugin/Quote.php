<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Plugin\Email as EmailPlugin;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class Quote
 * @package Aheadworks\Followupemail2\Model\Event\Plugin
 * @codeCoverageIgnore
 */
class Quote
{
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
    private $checkoutSession;

    /**
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        EventHistoryManagementInterface $eventHistoryManagement,
        Config $config,
        CheckoutSession $checkoutSession
    ) {
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Add quote to event history
     *
     * @param QuoteModel $subject
     * @param QuoteModel $quote
     * @return QuoteModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        QuoteModel $subject,
        $quote
    ) {
        $customerEmail = $quote->getCustomerEmail() ?
            $quote->getCustomerEmail() :
            $this->checkoutSession->getData(EmailPlugin::GUEST_EMAIL_KEY);

        if ($this->config->isEnabled()
            && $customerEmail
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
        return $quote;
    }
}
