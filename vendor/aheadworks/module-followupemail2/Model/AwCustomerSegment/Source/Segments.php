<?php
namespace Aheadworks\Followupemail2\Model\AwCustomerSegment\Source;

use Aheadworks\Followupemail2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Followupemail2\Model\AwCustomerSegment\Finder as AwCustomerSegmentFinder;
use Magento\Framework\Escaper;

/**
 * Class Segments
 *
 * @package Aheadworks\Followupemail2\Model\AwCustomerSegment\Source
 */
class Segments implements OptionSourceInterface
{
    /**
     * @var AwCustomerSegmentFinder
     */
    private $awCustomerSegmentFinder;

    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param AwCustomerSegmentFinder $awCustomerSegmentFinder
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param Escaper $escaper
     */
    public function __construct(
        AwCustomerSegmentFinder $awCustomerSegmentFinder,
        ThirdPartyModuleManager $thirdPartyModuleManager,
        Escaper $escaper
    ) {
        $this->awCustomerSegmentFinder = $awCustomerSegmentFinder;
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return $this->thirdPartyModuleManager->isAwCustomerSegmentationModuleEnabled()
            ? $this->getSegmentsOptionArray()
            : [];
    }

    /**
     * Retrieve segments option array
     *
     * @return array
     */
    private function getSegmentsOptionArray()
    {
        $optionArray = [];
        $segments = $this->awCustomerSegmentFinder->getSegments();
        foreach ($segments as $segment) {
            $preparedSegmentName = $this->escaper->escapeHtml($segment->getName());
            $optionArray[] = [
                'value' => $segment->getSegmentId(),
                'label' => $segment->getIsEnabled()
                    ? $preparedSegmentName
                    : $preparedSegmentName . __(' (Disabled)'),
            ];
        }
        return $optionArray;
    }
}
