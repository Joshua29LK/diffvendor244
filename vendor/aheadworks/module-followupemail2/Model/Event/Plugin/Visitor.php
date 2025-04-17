<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Visitor\Interceptor as VisitorInterceptor;
use Magento\Customer\Model\Visitor as VisitorModel;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Visitor
 * @package Aheadworks\Followupemail2\Model\Event\Plugin
 * @codeCoverageIgnore
 */
class Visitor
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        EventHistoryManagementInterface $eventHistoryManagement,
        Config $config,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add visitor to event history
     *
     * @param VisitorInterceptor $interceptor
     * @param VisitorModel $visitor
     * @return VisitorModel
     */
    public function afterSave(
        VisitorInterceptor $interceptor,
        VisitorModel $visitor
    ) {
        if ($this->config->isEnabled() &&
            $visitor->getCustomerId()
        ) {
            try {
                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->getById($visitor->getCustomerId());

                $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();

                $visitorData = array_merge($visitor->getData(), [
                    'email'             => $customer->getEmail(),
                    'store_id'          => $customer->getStoreId(),
                    'customer_group_id' => $customer->getGroupId(),
                    'customer_name'     => $customerName

                ]);
                $this->eventHistoryManagement->addEvent(EventInterface::TYPE_CUSTOMER_LAST_ACTIVITY, $visitorData);
            } catch (NoSuchEntityException $e) {
                // do nothing
            }
        }
        return $visitor;
    }
}
