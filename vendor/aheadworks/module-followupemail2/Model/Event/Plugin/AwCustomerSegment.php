<?php
namespace Aheadworks\Followupemail2\Model\Event\Plugin;

use Aheadworks\Followupemail2\Model\ResourceModel\Event as EventResourceModel;
use Aheadworks\CustomerSegmentation\Model\ResourceModel\Segment as SegmentResourceModel;
use Aheadworks\CustomerSegmentation\Model\Segment;

/**
 * Class AwCustomerSegment
 *
 * @package Aheadworks\Followupemail2\Model\Event\Plugin
 */
class AwCustomerSegment
{
    /**
     * @var EventResourceModel
     */
    private $eventResourceModel;

    /**
     * @param EventResourceModel $eventResourceModel
     */
    public function __construct(
        EventResourceModel $eventResourceModel
    ) {
        $this->eventResourceModel = $eventResourceModel;
    }

    /**
     * Clean links between event and the removed AW customer segment
     *
     * @param SegmentResourceModel $subject
     * @param SegmentResourceModel $result
     * @param Segment $segment
     * @return SegmentResourceModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        SegmentResourceModel $subject,
        SegmentResourceModel $result,
        Segment $segment
    ) {
        if ($segment && $segment->getId()) {
            $this->eventResourceModel->cleanLinksAfterAwCustomerSegmentRemoval($segment->getId());
        }
        return $result;
    }
}
