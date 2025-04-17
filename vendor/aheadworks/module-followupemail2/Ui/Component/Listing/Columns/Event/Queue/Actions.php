<?php
namespace Aheadworks\Followupemail2\Ui\Component\Listing\Columns\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Aheadworks\Followupemail2\Ui\Component\Listing\Columns\Event\Queue
 * @codeCoverageIgnore
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param EventRepositoryInterface $eventRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        EventQueueRepositoryInterface $eventQueueRepository,
        EventRepositoryInterface $eventRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->eventQueueRepository = $eventQueueRepository;
        $this->eventRepository = $eventRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['campaign'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'aw_followupemail2/event/index/send',
                        ['campaign_id' => $this->getCampaignId($item['event_queue_id'])]
                    ),
                    'label' => __('Go to campaign settings'),
                ];
                $item[$this->getData('name')]['cancel'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'aw_followupemail2/event_queue/cancel',
                        ['id' => $item['event_queue_id']]
                    ),
                    'label' => __('Cancel'),
                    'confirm' => [
                        'title' => __('Cancel'),
                        'message' => __('Cancel the email?')
                    ]
                ];
                $item[$this->getData('name')]['cancel_chain'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'aw_followupemail2/event_queue/cancelEvent',
                        ['id' => $item['event_queue_id']]
                    ),
                    'label' => __('Cancel this chain'),
                    'confirm' => [
                        'title' => __('Cancel Chain'),
                        'message' => __('Cancel the email chain?')
                    ]
                ];
                $item[$this->getData('name')]['send'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'aw_followupemail2/event_queue/send',
                        ['id' => $item['event_queue_id']]
                    ),
                    'label' => __('Send Now'),
                    'confirm' => [
                        'title' => __('Send Now'),
                        'message' => __('Send the email immediately?')
                    ]
                ];
            }
        }
        return $dataSource;
    }

    /**
     * Get campaign id
     *
     * @param int $eventQueueId
     * @return int|false
     */
    private function getCampaignId($eventQueueId)
    {
        try {
            /** @var EventQueueInterface $eventQueueItem */
            $eventQueueItem = $this->eventQueueRepository->get($eventQueueId);
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($eventQueueItem->getEventId());
            $campaignId = $event->getCampaignId();
        } catch (NoSuchEntityException $e) {
            $campaignId = false;
        }

        return $campaignId;
    }
}
