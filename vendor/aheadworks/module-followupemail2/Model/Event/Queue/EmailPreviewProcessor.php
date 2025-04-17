<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterfaceFactory;
use Aheadworks\Followupemail2\Model\Email\ContentResolver as EmailContentResolver;
use Aheadworks\Followupemail2\Model\Sender;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class EmailPreviewProcessor
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class EmailPreviewProcessor
{
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PreviewInterfaceFactory
     */
    private $previewFactory;

    /**
     * @var EmailContentResolver
     */
    private $emailContentResolver;

    /**
     * Sender
     */
    private $sender;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueManagementInterface $queueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PreviewInterfaceFactory $previewFactory
     * @param EmailContentResolver $emailContentResolver
     * @param Sender $sender
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     */
    public function __construct(
        QueueRepositoryInterface $queueRepository,
        QueueManagementInterface $queueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PreviewInterfaceFactory $previewFactory,
        EmailContentResolver $emailContentResolver,
        Sender $sender,
        Config $config,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->queueRepository = $queueRepository;
        $this->queueManagement = $queueManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->previewFactory = $previewFactory;
        $this->emailContentResolver = $emailContentResolver;
        $this->sender = $sender;
        $this->config = $config;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Get created email preview
     *
     * @param EventQueueEmailInterface $eventQueueEmail
     * @return PreviewInterface|false
     */
    public function getCreatedEmailPreview($eventQueueEmail)
    {
        $preview = false;
        try {
            $this->searchCriteriaBuilder
                ->addFilter(QueueInterface::EVENT_QUEUE_EMAIL_ID, $eventQueueEmail->getId(), 'eq');

            /** @var QueueInterface[] $queueItems */
            $queueItems = $this->queueRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();

            if (count($queueItems) > 0) {
                $queueItem = reset($queueItems);
                /** @var PreviewInterface $preview */
                $preview = $this->queueManagement->getPreview($queueItem);
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
        }

        return $preview;
    }

    /**
     * Get scheduled email preview
     *
     * @param EventQueueInterface $eventQueueItem
     * @param EmailInterface $email
     * @return PreviewInterface|false
     */
    public function getScheduledEmailPreview($eventQueueItem, $email)
    {
        $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
        $storeId = $eventData['store_id'];

        $content = $this->emailContentResolver->getCurrentContent($email);
        try {
            $renderedEmail = $this->sender->renderEventQueueItem($eventQueueItem, $content);

            /** @var PreviewInterface $preview */
            $preview = $this->previewFactory->create();
            $preview
                ->setStoreId($storeId)
                ->setSenderName($content->getSenderName() ?
                    $content->getSenderName() :
                    $this->config->getSenderName($storeId))
                ->setSenderEmail($content->getSenderEmail() ?
                    $content->getSenderEmail() :
                    $this->config->getSenderEmail($storeId))
                ->setRecipientName($renderedEmail['recipient_name'])
                ->setRecipientEmail($renderedEmail['recipient_email'])
                ->setSubject($renderedEmail['subject'])
                ->setContent($renderedEmail['content']);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            $preview = false;
        }

        return $preview;
    }
}
