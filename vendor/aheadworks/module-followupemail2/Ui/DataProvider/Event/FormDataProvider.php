<?php
namespace Aheadworks\Followupemail2\Ui\DataProvider\Event;

use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class FormDataProvider
 * @package Aheadworks\Followupemail2\Ui\DataProvider\Event
 * @codeCoverageIgnore
 */
class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EventRepositoryInterface $eventRepository
     * @param RequestInterface $request
     * @param DataObjectProcessor $dataObjectProcessor
     * @param EventTypePool $eventTypePool
     * @param CampaignManagementInterface $campaignManagement
     * @param Serializer $serializer
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EventRepositoryInterface $eventRepository,
        RequestInterface $request,
        DataObjectProcessor $dataObjectProcessor,
        EventTypePool $eventTypePool,
        CampaignManagementInterface $campaignManagement,
        Serializer $serializer,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->eventRepository = $eventRepository;
        $this->request = $request;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->eventTypePool = $eventTypePool;
        $this->campaignManagement = $campaignManagement;
        $this->serializer = $serializer;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $id = $this->request->getParam($this->getRequestFieldName());
        $duplicate = $this->request->getParam('duplicate');
        $data = [];
        if ($id) {
            try {
                /** @var EventInterface $eventDataObject */
                $eventDataObject = $this->eventRepository->get($id);

                $formData = $this->dataObjectProcessor->buildOutputDataArray(
                    $eventDataObject,
                    EventInterface::class
                );

                $formData = $this->convertToString(
                    $formData,
                    [
                        EventInterface::STATUS,
                        EventInterface::STORE_IDS,
                        EventInterface::NEWSLETTER_ONLY,
                        EventInterface::FAILED_EMAILS_MODE,
                        EventInterface::LIFETIME_CONDITIONS,
                        EventInterface::AW_CUSTOMER_SEGMENT_IDS,
                    ]
                );

                if ($duplicate) {
                    unset($formData[EventInterface::ID]);
                    $formData[EventInterface::STATUS] = EventInterface::STATUS_DISABLED;
                    $formData[EventInterface::NAME] .= ' #1';
                    $formData['duplicate_id'] = $id;
                    $data[$id] = $formData;
                } else {
                    $data[$id] = $formData;
                }
            } catch (NoSuchEntityException $e) {
            }
        } else {
            if (isset($this->data['config']['params'])) {
                $params = $this->data['config']['params'];
                $paramValues = [];
                foreach ($params as $param) {
                    $paramValue = $this->request->getParam($param);
                    if ($paramValue) {
                        $paramValues[$param] = $paramValue;
                    }
                }
                if (!isset($paramValues[EventInterface::EVENT_TYPE])) {
                    $eventTypeCode = $this->eventTypePool->getDefaultTypeCode();
                    $paramValues[EventInterface::EVENT_TYPE] = $eventTypeCode;
                }
                $eventName = $this->campaignManagement->getNewEventName(
                    $paramValues[EventInterface::CAMPAIGN_ID],
                    $paramValues[EventInterface::EVENT_TYPE]
                );
                $paramValues[EventInterface::NAME] = $eventName;

                $data[null] = $paramValues;
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }

    /**
     * Convert selected fields to string
     *
     * @param [] $data
     * @param string[] $fields
     * @return []
     */
    private function convertToString($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                if ($field == EventInterface::LIFETIME_CONDITIONS) {
                    $conditionData = $this->serializer->unserialize($data[$field]);
                    $data['lifetime_conditions'] = isset($conditionData['operator']) ?
                        $conditionData['operator'] : null;
                    $data['lifetime_value'] = isset($conditionData['params']['value']) ?
                        $conditionData['params']['value'] : null;
                    $data['lifetime_from'] = isset($conditionData['params']['from']) ?
                        $conditionData['params']['from'] : null;
                    $data['lifetime_to'] = isset($conditionData['params']['to']) ?
                        $conditionData['params']['to'] : null;
                } elseif (is_array($data[$field])) {
                    foreach ($data[$field] as $key => $value) {
                        if ($value === false) {
                            $data[$field][$key] = '0';
                        } else {
                            $data[$field][$key] = (string)$value;
                        }
                    }
                } else {
                    $data[$field] = (string)$data[$field];
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Is event type element enabled
     *
     * @param string $element
     * @return bool
     */
    public function isElementEnabled($element)
    {
        return $this->getEventTypeInstance()->isElementEnabled($element);
    }

    /**
     * Is event type allowed for guest users
     *
     * @return bool
     */
    public function isAllowedForGuests()
    {
        return $this->getEventTypeInstance()->getAllowedForGuests();
    }

    /**
     * Retrieve event type instance
     *
     * @return EventTypeInterface
     */
    private function getEventTypeInstance()
    {
        $eventTypeCode = null;
        $id = $this->request->getParam(EventInterface::ID);
        if ($id) {
            try {
                /** @var EventInterface $eventDataObject */
                $eventDataObject = $this->eventRepository->get($id);
                $eventTypeCode = $eventDataObject->getEventType();
            } catch (NoSuchEntityException $e) {
            }
        } else {
            $eventTypeCode = $this->request->getParam(EventInterface::EVENT_TYPE);
        }
        if (!$eventTypeCode) {
            $eventTypeCode = $this->eventTypePool->getDefaultTypeCode();
        }
        /** @var EventTypeInterface $eventType */
        $eventType = $this->eventTypePool->getType($eventTypeCode);
        return $eventType;
    }
}
