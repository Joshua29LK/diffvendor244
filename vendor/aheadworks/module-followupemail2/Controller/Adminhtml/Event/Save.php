<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterfaceFactory;
use Aheadworks\Followupemail2\Ui\DataProvider\Event\ManageFormProcessor;
use Aheadworks\Followupemail2\Model\Event\TypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Save
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::campaigns_actions';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var ManageFormProcessor
     */
    private $manageFormProcessor;

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventManagementInterface $eventManagement
     * @param EventInterfaceFactory $eventFactory
     * @param ManageFormProcessor $manageFormProcessor
     * @param EventTypePool $eventTypePool
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param PostDataProcessor $postDataProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventManagementInterface $eventManagement,
        EventInterfaceFactory $eventFactory,
        ManageFormProcessor $manageFormProcessor,
        EventTypePool $eventTypePool,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        PostDataProcessor $postDataProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->campaignManagement = $campaignManagement;
        $this->eventRepository = $eventRepository;
        $this->eventManagement = $eventManagement;
        $this->eventFactory = $eventFactory;
        $this->manageFormProcessor = $manageFormProcessor;
        $this->eventTypePool = $eventTypePool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->postDataProcessor = $postDataProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = [
            'error'     => true,
            'message'   => __('No data specified!')
        ];
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $id = $data['id'] ?? false;

                $preparedData = $this->postDataProcessor->prepareEntityData($data);

                /** @var EventInterface $eventDataObject */
                $eventDataObject = $id
                    ? $this->eventRepository->get($id)
                    : $this->eventFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $eventDataObject,
                    $preparedData,
                    EventInterface::class
                );

                $eventDataObject = $this->eventRepository->save($eventDataObject);

                if (isset($data['duplicate_id']) && $data['duplicate_id']) {
                    $this->eventManagement->duplicateEventEmails(
                        $data['duplicate_id'],
                        $eventDataObject->getId()
                    );
                }

                $eventData = $this->dataObjectProcessor->buildOutputDataArray(
                    $eventDataObject,
                    EventInterface::class
                );
                /** @var TypeInterface $eventType */
                $eventType = $this->eventTypePool->getType($eventDataObject->getEventType());
                $eventData['event_type_label'] = $eventType->getTitle();
                $eventData['emails'] = $this->manageFormProcessor->getEventEmailsData($eventDataObject->getId());
                $eventData['totals'] = $this->manageFormProcessor->getEventTotals($eventDataObject->getId());

                $result = [
                    'error'     => false,
                    'message'   => __('Success.'),
                    'event'     => $eventData,
                    'create'    => $id ? false : true,
                    'events_count' => $this->campaignManagement->getEventsCount($eventDataObject->getCampaignId()),
                    'emails_count' => $this->campaignManagement->getEmailsCount($eventDataObject->getCampaignId()),
                ];
            } catch (\Exception $e) {
                $result = [
                    'error'     => true,
                    'message'   => __($e->getMessage())
                ];
            }
        }
        return $resultJson->setData($result);
    }
}
