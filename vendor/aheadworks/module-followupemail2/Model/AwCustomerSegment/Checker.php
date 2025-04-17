<?php
namespace Aheadworks\Followupemail2\Model\AwCustomerSegment;

use Aheadworks\CustomerSegmentation\Model\ResourceModel\Customer
    as CustomerSegmentationCustomerResourceModel;
use Aheadworks\CustomerSegmentation\Api\Data\CustomerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Checker
 *
 * @package Aheadworks\Followupemail2\Model\AwCustomerSegment
 */
class Checker
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * Check if customer belongs to the specific segment
     *
     * @param int $segmentId
     * @param int $storeId
     * @param int|null $customerId
     * @param string $customerEmail
     * @return bool
     */
    public function isCustomerBelongToSegment($segmentId, $storeId, $customerId, $customerEmail)
    {
        try {
            /** @var CustomerSegmentationCustomerResourceModel $customerResourceModel */
            $customerResourceModel = $this->objectManager->create(
                CustomerSegmentationCustomerResourceModel::class
            );
            if (empty($customerId)) {
                $criteriaField = CustomerInterface::EMAIL;
                $criteriaValue = $customerEmail;
            } else {
                $criteriaField = CustomerInterface::CUSTOMER_ID;
                $criteriaValue = $customerId;
            }
            $result = $customerResourceModel->isExists(
                $segmentId,
                $storeId,
                $criteriaField,
                $criteriaValue
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $result = false;
        }
        return $result;
    }
}
