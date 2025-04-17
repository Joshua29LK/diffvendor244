<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;

/**
 * Class CustomerReview
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class CustomerReview extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_CUSTOMER_REVIEW;

    /**
     * @var string
     */
    protected $referenceDataKey = 'review_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'review';

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param ReviewFactory $reviewFactory
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        ReviewFactory $reviewFactory,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct(
            $campaignManagement,
            $eventRepository,
            $eventHistoryRepository,
            $eventValidator,
            $eventQueueManagement,
            $searchCriteriaBuilder,
            $serializer
        );
        $this->reviewFactory = $reviewFactory;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        $eventdata = $this->serializer->unserialize($eventHistoryItem->getEventData());
        /** @var Review $review */
        $review = $this->getEventObject($eventdata);
        if (!$review || !$review->isApproved()) {
            $this->eventHistoryRepository->delete($eventHistoryItem);
        } else {
            parent::process($eventHistoryItem);
        }
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Review|null
     */
    public function getEventObject($eventData)
    {
        $review = $this->reviewFactory->create()->load($eventData[$this->getReferenceDataKey()]);
        if (!$review->getId()) {
            return null;
        }

        $items = [];
        try {
            /** @var DataObject $item */
            $item = $this->dataObjectFactory->create();

            /** @var ProductInterface $product */
            $product = $this->productRepository->getById($eventData['product_id']);
            $item
                ->setParentItemId(null)
                ->setProductType($product->getTypeId())
                ->setProduct($product);

            $items[] = $item;
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        $review->setItems($items);
        return $review;
    }

    /**
     * {@inheritdoc}
     */
    public function validateEventData(array $data = [])
    {
        if (!array_key_exists('product_id', $data)) {
            return false;
        }

        return parent::validateEventData($data);
    }
}
