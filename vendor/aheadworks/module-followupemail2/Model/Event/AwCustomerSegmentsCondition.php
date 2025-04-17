<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Aheadworks\Followupemail2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Aheadworks\Followupemail2\Model\AwCustomerSegment\Finder as AwCustomerSegmentFinder;
use Aheadworks\Followupemail2\Model\AwCustomerSegment\Checker as AwCustomerSegmentChecker;

/**
 * Class AwCustomerSegmentsCondition
 *
 * @package Aheadworks\Followupemail2\Model\Event
 */
class AwCustomerSegmentsCondition
{
    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @var AwCustomerSegmentFinder
     */
    private $awCustomerSegmentFinder;

    /**
     * @var AwCustomerSegmentChecker
     */
    private $awCustomerSegmentChecker;

    /**
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param AwCustomerSegmentFinder $awCustomerSegmentFinder
     * @param AwCustomerSegmentChecker $awCustomerSegmentChecker
     */
    public function __construct(
        ThirdPartyModuleManager $thirdPartyModuleManager,
        AwCustomerSegmentFinder $awCustomerSegmentFinder,
        AwCustomerSegmentChecker $awCustomerSegmentChecker
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->awCustomerSegmentFinder = $awCustomerSegmentFinder;
        $this->awCustomerSegmentChecker = $awCustomerSegmentChecker;
    }

    /**
     * Check if customer belongs to at least one of specified segment
     *
     * @param int|null $customerId
     * @param string $customerEmail
     * @param int $storeId
     * @param array $segmentIds
     * @return bool
     */
    public function validate($customerId, $customerEmail, $storeId, $segmentIds)
    {
        $result = true;
        if ($this->thirdPartyModuleManager->isAwCustomerSegmentationModuleEnabled()
            && !empty($segmentIds)
        ) {
            $segments = $this->awCustomerSegmentFinder->getSegments($segmentIds);
            if (count($segments)) {
                $result = false;
                foreach ($segments as $segment) {
                    if ($segment->getIsEnabled()) {
                        $isCustomerBelongToSegment = $this->awCustomerSegmentChecker->isCustomerBelongToSegment(
                            $segment->getSegmentId(),
                            $storeId,
                            $customerId,
                            $customerEmail
                        );
                        if ($isCustomerBelongToSegment) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }
}
