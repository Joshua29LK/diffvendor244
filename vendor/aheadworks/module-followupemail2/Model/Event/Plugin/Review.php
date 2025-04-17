<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Review\Model\Review as ReviewModel;

/**
 * Class Review
 * @package Aheadworks\Followupemail2\Model\Plugin
 */
class Review
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EventHistoryManagementInterface
     */
    private $eventHistoryManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Config $config
     * @param EventHistoryManagementInterface $eventHistoryManagement
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $config,
        EventHistoryManagementInterface $eventHistoryManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->config = $config;
        $this->eventHistoryManagement = $eventHistoryManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add a review to the event history
     *
     * @param ReviewModel $subject
     * @param ReviewModel $result
     * @return ReviewModel
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        ReviewModel $subject,
        ReviewModel $result
    ) {
        if ($this->config->isEnabled() && $result->getCustomerId()) {
            if ($result->isApproved()) {
                try {
                    /** @var CustomerInterface $customer */
                    $customer = $this->customerRepository->getById($result->getCustomerId());
                    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerGroupId = $customer->getGroupId();

                    $reviewData = array_merge($result->getData(), [
                        'email'             => $customer->getEmail(),
                        'customer_name'     => $customerName,
                        'customer_group_id' => $customerGroupId,
                        'product_id'        => $result->getEntityPkValue()
                    ]);

                    $this->eventHistoryManagement->addEvent(EventInterface::TYPE_CUSTOMER_REVIEW, $reviewData);
                } catch (NoSuchEntityException $e) {
                }
            }
        }

        return $result;
    }
}
