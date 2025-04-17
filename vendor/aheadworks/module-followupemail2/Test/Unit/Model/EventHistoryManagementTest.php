<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\EventHistoryManagement;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\EventHistoryManagement
 */
class EventHistoryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventHistoryManagement
     */
    private $model;

    /**
     * @var EventTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypePoolMock;

    /**
     * @var EventHistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryFactoryMock;

    /**
     * @var EventHistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryRepositoryMock;

    /**
     * @var EventQueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueManagementMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Serializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventTypePoolMock = $this->getMockBuilder(EventTypePool::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryFactoryMock = $this->getMockBuilder(EventHistoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryRepositoryMock = $this->getMockBuilder(EventHistoryRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueManagementMock = $this->getMockBuilder(EventQueueManagementInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            EventHistoryManagement::class,
            [
                'eventTypePool' => $this->eventTypePoolMock,
                'eventHistoryFactory' => $this->eventHistoryFactoryMock,
                'eventHistoryRepository' => $this->eventHistoryRepositoryMock,
                'eventQueueManagement' => $this->eventQueueManagementMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'logger' => $this->loggerMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test addEvent method
     */
    public function testAddEvent()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $refDataKey = 'entity_id';
        $refDataKeyValue = 123;
        $eventData = [
            $refDataKey => $refDataKeyValue,
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('validateEventData')
            ->with($eventData)
            ->willReturn(true);
        $eventHandlerMock->expects($this->once())
            ->method('cancelEvents')
            ->with($eventType, $eventData);
        $eventHandlerMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventType);
        $eventHandlerMock->expects($this->atLeastOnce())
            ->method('getReferenceDataKey')
            ->willReturn($refDataKey);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventHistoryInterface::REFERENCE_ID, $refDataKeyValue, 'eq'],
                [EventHistoryInterface::EVENT_TYPE, $eventType, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventHistoryMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $eventHistoryMock->expects($this->once())
            ->method('getProcessed')
            ->willReturn(false);
        $eventHistoryMock->expects($this->once())
            ->method('setReferenceId')
            ->with($refDataKeyValue)
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($eventData)
            ->willReturn(json_encode($eventData));
        $eventHistoryMock->expects($this->once())
            ->method('setEventData')
            ->with(json_encode($eventData))
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setProcessed')
            ->with(false)
            ->willReturnSelf();
        $eventHistorySearchResultsMock = $this->getMockBuilder(EventHistorySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventHistorySearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventHistoryMock]);

        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventHistorySearchResultsMock);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);

        $this->assertTrue($this->model->addEvent($eventType, $eventData));
    }

    /**
     * Test addEvent method if event data is not valid
     */
    public function testAddEventNoValidEventData()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $refDataKey = 'entity_id';
        $refDataKeyValue = 123;
        $eventData = [
            $refDataKey => $refDataKeyValue,
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('validateEventData')
            ->with($eventData)
            ->willReturn(false);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $this->assertFalse($this->model->addEvent($eventType, $eventData));
    }

    /**
     * Test addEvent method if there is already processed an event history with the same reference id
     */
    public function testAddEventAlreadyProcessed()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $refDataKey = 'entity_id';
        $refDataKeyValue = 123;
        $eventData = [
            $refDataKey => $refDataKeyValue,
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('validateEventData')
            ->with($eventData)
            ->willReturn(true);
        $eventHandlerMock->expects($this->once())
            ->method('cancelEvents')
            ->with($eventType, $eventData);
        $eventHandlerMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventType);
        $eventHandlerMock->expects($this->atLeastOnce())
            ->method('getReferenceDataKey')
            ->willReturn($refDataKey);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventHistoryInterface::REFERENCE_ID, $refDataKeyValue, 'eq'],
                [EventHistoryInterface::EVENT_TYPE, $eventType, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventHistoryMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $eventHistoryMock->expects($this->once())
            ->method('getProcessed')
            ->willReturn(true);
        $eventHistoryMock->expects($this->once())
            ->method('setReferenceId')
            ->with($refDataKeyValue)
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($eventData)
            ->willReturn(json_encode($eventData));
        $eventHistoryMock->expects($this->once())
            ->method('setEventData')
            ->with(json_encode($eventData))
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setProcessed')
            ->with(false)
            ->willReturnSelf();
        $eventHistorySearchResultsMock = $this->getMockBuilder(EventHistorySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventHistorySearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventHistoryMock]);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventHistorySearchResultsMock);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($eventHistoryMock)
            ->willReturn(true);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);

        $this->assertTrue($this->model->addEvent($eventType, $eventData));
    }

    /**
     * Test addEvent method if there is no event history items with the same reference id
     */
    public function testAddEventFirstEventHistory()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $refDataKey = 'entity_id';
        $refDataKeyValue = 123;
        $eventData = [
            $refDataKey => $refDataKeyValue,
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('validateEventData')
            ->with($eventData)
            ->willReturn(true);
        $eventHandlerMock->expects($this->once())
            ->method('cancelEvents')
            ->with($eventType, $eventData);
        $eventHandlerMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventType);
        $eventHandlerMock->expects($this->atLeastOnce())
            ->method('getReferenceDataKey')
            ->willReturn($refDataKey);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventHistoryInterface::REFERENCE_ID, $refDataKeyValue, 'eq'],
                [EventHistoryInterface::EVENT_TYPE, $eventType, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventHistoryMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $eventHistoryMock->expects($this->once())
            ->method('setReferenceId')
            ->with($refDataKeyValue)
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($eventData)
            ->willReturn(json_encode($eventData));
        $eventHistoryMock->expects($this->once())
            ->method('setEventData')
            ->with(json_encode($eventData))
            ->willReturnSelf();
        $eventHistoryMock->expects($this->once())
            ->method('setProcessed')
            ->with(false)
            ->willReturnSelf();
        $eventHistorySearchResultsMock = $this->getMockBuilder(EventHistorySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventHistorySearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventHistorySearchResultsMock);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);

        $this->assertTrue($this->model->addEvent($eventType, $eventData));
    }

    /**
     * Test processUnprocessedItems method
     */
    public function testProcessUnprocessedItems()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $maxItemsCount = 1;

        $eventHistoryMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $eventHistoryMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('process')
            ->with($eventHistoryMock);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EventHistoryInterface::PROCESSED, false)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventHistorySearchResultsMock = $this->getMockBuilder(EventHistorySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventHistorySearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventHistoryMock]);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventHistorySearchResultsMock);

        $this->assertTrue($this->model->processUnprocessedItems($maxItemsCount));
    }

    /**
     * Test processUnprocessedItems method if an error occurs
     */
    public function testProcessUnprocessedItemsError()
    {
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $maxItemsCount = 1;
        $errorExceptionMessage = 'Error!';

        $eventHistoryMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $eventHistoryMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('process')
            ->with($eventHistoryMock)
            ->willThrowException(new \Exception($errorExceptionMessage));
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EventHistoryInterface::PROCESSED, false)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventHistorySearchResultsMock = $this->getMockBuilder(EventHistorySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventHistorySearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventHistoryMock]);
        $this->eventHistoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventHistorySearchResultsMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($errorExceptionMessage);

        $this->assertFalse($this->model->processUnprocessedItems($maxItemsCount));
    }
}
