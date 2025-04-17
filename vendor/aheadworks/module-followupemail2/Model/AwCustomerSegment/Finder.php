<?php
namespace Aheadworks\Followupemail2\Model\AwCustomerSegment;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\CustomerSegmentation\Api\Data\SegmentInterface;
use Aheadworks\CustomerSegmentation\Api\SegmentRepositoryInterface;
use Aheadworks\CustomerSegmentation\Api\Data\SegmentSearchResultsInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Finder
 *
 * @package Aheadworks\Followupemail2\Model\AwCustomerSegment
 */
class Finder
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Retrieve array of segments
     *
     * @param array $segmentIds
     * @param int|null $storeId
     * @return SegmentInterface[]
     */
    public function getSegments($segmentIds = [], $storeId = null)
    {
        try {
            /** @var SegmentRepositoryInterface $segmentRepository */
            $segmentRepository = $this->objectManager->create(SegmentRepositoryInterface::class);
            if (count($segmentIds)) {
                $this->searchCriteriaBuilder->addFilter(
                    SegmentInterface::SEGMENT_ID,
                    $segmentIds,
                    'in'
                );
            }
            if (isset($storeId)) {
                $this->searchCriteriaBuilder->addFilter(
                    'website_id',
                    $this->storeManager->getStore($storeId)->getWebsiteId()
                );
            }
            /** @var SegmentSearchResultsInterface $searchResults */
            $searchResults = $segmentRepository->getList($this->searchCriteriaBuilder->create());
            $segments = $searchResults->getItems();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $segments = [];
        }
        return $segments;
    }
}
