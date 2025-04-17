<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Delete
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event
 */
class Delete extends \Magento\Backend\App\Action
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
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param StatisticsManagementInterface $statisticsManagement
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        StatisticsManagementInterface $statisticsManagement,
        DataObjectProcessor $dataObjectProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->campaignManagement = $campaignManagement;
        $this->eventRepository = $eventRepository;
        $this->statisticsManagement = $statisticsManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var EventInterface $event */
                $event = $this->eventRepository->get($id);

                $this->eventRepository->deleteById($id);

                $campaignStats = $this->statisticsManagement->getByCampaignId($event->getCampaignId());
                $campaignStatsData = $this->dataObjectProcessor->buildOutputDataArray(
                    $campaignStats,
                    StatisticsInterface::class
                );

                $result = [
                    'error'     => false,
                    'message'   => __('Success.'),
                    'events_count' => $this->campaignManagement->getEventsCount($event->getCampaignId()),
                    'emails_count' => $this->campaignManagement->getEmailsCount($event->getCampaignId()),
                    'campaign_stats' => $campaignStatsData,
                ];
            } catch (\Exception $e) {
                $result = [
                    'error'     => true,
                    'message'   => __($e->getMessage())
                ];
            }
        } else {
            $result = [
                'error'     => true,
                'message'   => __('Event Id is not specified!')
            ];
        }
        return $resultJson->setData($result);
    }
}
