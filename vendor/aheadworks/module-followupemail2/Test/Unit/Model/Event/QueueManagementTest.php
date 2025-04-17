<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Model\Event\QueueManagement;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\CodeGenerator;
use Aheadworks\Followupemail2\Model\Serializer;
use Aheadworks\Followupemail2\Model\Unsubscribe\Service as UnsubscribeService;
use Aheadworks\Followupemail2\Model\Event\Queue\ItemProcessor as EventQueueItemProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\QueueManagement
 */
class QueueManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueManagement
     */
    private $model;

    /**
     * @var EventQueueInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueFactoryMock;

    /**
     * @var EventQueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueRepositoryMock;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * @var CodeGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $codeGeneratorMock;

    /**
     * @var UnsubscribeService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeServiceMock;

    /**
     * @var EventQueueItemProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueItemProcessorMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

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

        $this->eventQueueFactoryMock = $this->getMockBuilder(EventQueueInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventQueueRepositoryMock = $this->getMockBuilder(EventQueueRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();
        $this->codeGeneratorMock = $this->getMockBuilder(CodeGenerator::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->unsubscribeServiceMock = $this->getMockBuilder(UnsubscribeService::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventQueueItemProcessorMock = $this->getMockBuilder(EventQueueItemProcessor::class)
            ->setMethods(['process', 'cancelScheduledEmail', 'sendNextScheduledEmail', 'getScheduledEmailPreview'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter', 'setPageSize', 'setCurrentPage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            QueueManagement::class,
            [
                'eventQueueFactory' => $this->eventQueueFactoryMock,
                'eventQueueRepository' => $this->eventQueueRepositoryMock,
                'eventManagement' => $this->eventManagementMock,
                'codeGenerator' => $this->codeGeneratorMock,
                'unsubscribeService' => $this->unsubscribeServiceMock,
                'eventQueueItemProcessor' => $this->eventQueueItemProcessorMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test cancelEvents method
     */
    public function testCancelEvents()
    {
        $eventCode = EventInterface::TYPE_ABANDONED_CART;
        $referenceId = 1;
        $eventQueueId = 10;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_TYPE, $eventCode, 'eq'],
                [EventQueueInterface::REFERENCE_ID, $referenceId, 'eq'],
                [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueItemMock)
            ->willReturn(true);

        $this->assertTrue($this->model->cancelEvents($eventCode, $referenceId));
    }

    /**
     * Test cancelEvents method if some emails already created
     */
    public function testCancelEventsWithEmails()
    {
        $eventCode = EventInterface::TYPE_ABANDONED_CART;
        $referenceId = 1;
        $eventQueueId = 10;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_TYPE, $eventCode, 'eq'],
                [EventQueueInterface::REFERENCE_ID, $referenceId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$emailMock]);
        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueInterface::STATUS_CANCELLED)
            ->willReturnSelf();
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertTrue($this->model->cancelEvents($eventCode, $referenceId));
    }

    /**
     * Test cancelEventsByCampaignId method
     */
    public function testCancelEventsByCampaignId()
    {
        $campaignId = 1;
        $eventId = 2;
        $eventQueueId = 100;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventId);
        $this->eventManagementMock->expects($this->once())
            ->method('getEventsByCampaignId')
            ->with($campaignId)
            ->willReturn([$eventMock]);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_ID, [$eventId], 'in'],
                [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueItemMock)
            ->willReturn(true);

        $this->assertTrue($this->model->cancelEventsByCampaignId($campaignId));
    }

    /**
     * Test cancelEventsByCampaignId method if some emails already created
     */
    public function testCancelEventsByCampaignIdWithEmails()
    {
        $campaignId = 1;
        $eventId = 2;
        $eventQueueId = 100;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventId);
        $this->eventManagementMock->expects($this->once())
            ->method('getEventsByCampaignId')
            ->with($campaignId)
            ->willReturn([$eventMock]);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_ID, [$eventId], 'in'],
                [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$emailMock]);
        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueInterface::STATUS_CANCELLED)
            ->willReturnSelf();
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertTrue($this->model->cancelEventsByCampaignId($campaignId));
    }

    /**
     * Test cancelEventsByEventId method
     * @dataProvider cancelEventsByEventIdDataprovider
     */
    public function testCancelEventsByEventId($referenceId)
    {
        $eventId = 1;
        $eventQueueId = 100;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!$referenceId) {
            $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
                ->method('addFilter')
                ->withConsecutive(
                    [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                    [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq']
                )
                ->willReturnSelf();
        } else {
            $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
                ->method('addFilter')
                ->withConsecutive(
                    [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                    [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq'],
                    [EventQueueInterface::REFERENCE_ID, $referenceId]
                )
                ->willReturnSelf();
        }

        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueItemMock)
            ->willReturn(true);

        $this->assertTrue($this->model->cancelEventsByEventId($eventId));
    }

    /**
     * @return array
     */
    public function cancelEventsByEventIdDataprovider()
    {
        return [
            [null], [10]
        ];
    }

    /**
     * Test cancelEventsByEventId method if some emails already created
     * @dataProvider cancelEventsByEventIdDataprovider
     */
    public function testCancelEventsByEventIdWithEmails($referenceId)
    {
        $eventId = 1;
        $eventQueueId = 100;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!$referenceId) {
            $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
                ->method('addFilter')
                ->withConsecutive(
                    [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                    [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq']
                )
                ->willReturnSelf();
        } else {
            $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
                ->method('addFilter')
                ->withConsecutive(
                    [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                    [EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING, 'eq'],
                    [EventQueueInterface::REFERENCE_ID, $referenceId]
                )
                ->willReturnSelf();
        }

        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$emailMock]);
        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueInterface::STATUS_CANCELLED)
            ->willReturnSelf();
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertTrue($this->model->cancelEventsByEventId($eventId));
    }

    /**
     * Test cancelScheduledEmail method
     *
     * @param EventQueueInterface $eventQueueItemMock
     * @param bool $result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider eventQueueItemDataProvider
     */
    public function testCancelScheduledEmail($eventQueueItemMock, $result)
    {
        $eventQueueId = 100;

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);

        if ($result) {
            $this->eventQueueItemProcessorMock->expects($this->once())
                ->method('cancelScheduledEmail')
                ->with($eventQueueItemMock)
                ->willReturn($eventQueueItemMock);
        }

         $this->assertEquals($result, $this->model->cancelScheduledEmail($eventQueueId));
    }

    /**
     * @return array
     */
    public function eventQueueItemDataProvider()
    {
        return [
            [
                'eventQueueItemMock' => $this->getEventQueueItemMock(EventQueueInterface::STATUS_PROCESSING),
                'result' => true
            ],
            [
                'eventQueueItemMock' => $this->getEventQueueItemMock(EventQueueInterface::STATUS_CANCELLED),
                'result' => false
            ],
            [
                'eventQueueItemMock' => $this->getEventQueueItemMock(EventQueueInterface::STATUS_FINISHED),
                'result' => false
            ],
        ];
    }

    /**
     * Get event queue item mock
     *
     * @param int $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventQueueItemMock($status)
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        return $eventQueueItemMock;
    }

    /**
     * Test cancelScheduledEmail method if an error occurs
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email can not be cancelled.
     */
    public function testCancelScheduledEmailError()
    {
        $eventQueueId = 100;

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willThrowException(new \Exception('Error!'));
        $this->expectException(LocalizedException::class);
        $this->model->cancelScheduledEmail($eventQueueId);
    }

    /**
     * Test cancelEvent method
     */
    public function testCancelEvent()
    {
        $eventQueueId = 1;

        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EventQueueInterface::STATUS_PROCESSING);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$eventQueueEmailMock]);
        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->willReturn(EventQueueInterface::STATUS_CANCELLED);

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertTrue($this->model->cancelEvent($eventQueueId));
    }

    /**
     * Test cancelEvent method if no emails are sent
     */
    public function testCancelEventNoEmails()
    {
        $eventQueueId = 1;

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EventQueueInterface::STATUS_PROCESSING);
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);
        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('delete')
            ->with($eventQueueItemMock)
            ->willReturn(true);

        $this->assertTrue($this->model->cancelEvent($eventQueueId));
    }

    /**
     * Test cancelEvent method if event queue has not processing status
     */
    public function testCancelEventNotProcessing()
    {
        $eventQueueId = 1;

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EventQueueInterface::STATUS_FINISHED);

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);

        $this->assertFalse($this->model->cancelEvent($eventQueueId));
    }

    /**
     * Test cancelEvent method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email chain can not be cancelled.
     */
    public function testCancelEventException()
    {
        $eventQueueId = 1;

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        $this->expectException(LocalizedException::class);
        $this->model->cancelEvent($eventQueueId);
    }

    /**
     * Test sendNextScheduledEmail method
     * @param EventQueueInterface $eventQueueItemMock
     * @param bool $result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider eventQueueItemDataProvider
     */
    public function testSendNextScheduledEmail($eventQueueItemMock, $result)
    {
        $eventQueueId = 1;

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);

        if ($result) {
            $this->eventQueueItemProcessorMock->expects($this->once())
                ->method('sendNextScheduledEmail')
                ->with($eventQueueItemMock)
                ->willReturn(true);
        }

        $this->assertEquals($result, $this->model->sendNextScheduledEmail($eventQueueId));
    }

    /**
     * Test sendNextScheduledEmail method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email can not be sent.
     */
    public function testSendNextScheduledEmailException()
    {
        $eventQueueId = 1;

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        $this->expectException(LocalizedException::class);
        $this->model->sendNextScheduledEmail($eventQueueId);
    }

    /**
     * Test getScheduledEmailPreview method
     */
    public function testGetScheduledEmailPreview()
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueItemProcessorMock->expects($this->once())
            ->method('getScheduledEmailPreview')
            ->with($eventQueueItemMock)
            ->willReturn($previewMock);

        $this->assertSame($previewMock, $this->model->getScheduledEmailPreview($eventQueueItemMock));
    }

    /**
     * Test getScheduledEmailPreview method if no preview returns
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Email preview can not be created.
     */
    public function testGetScheduledEmailPreviewNoPreview()
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueItemProcessorMock->expects($this->once())
            ->method('getScheduledEmailPreview')
            ->with($eventQueueItemMock)
            ->willReturn(false);
        $this->expectException(LocalizedException::class);
        $this->model->getScheduledEmailPreview($eventQueueItemMock);
    }

    /**
     * Test getScheduledEmailPreview method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Email preview can not be created.
     */
    public function testGetScheduledEmailPreviewException()
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueItemProcessorMock->expects($this->once())
            ->method('getScheduledEmailPreview')
            ->with($eventQueueItemMock)
            ->willThrowException(new \Exception('Error!'));
        $this->expectException(LocalizedException::class);
        $this->model->getScheduledEmailPreview($eventQueueItemMock);
    }

    /**
     * Test add method
     */
    public function testAdd()
    {
        $storeId = 1;
        $eventId = 2;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $email = 'test@example.com';
        $eventData = json_encode(
            [
                'store_id' => $storeId,
                'email' => $email,
            ]
        );
        $referenceId = 10;
        $newEventStatus = EventQueueInterface::STATUS_PROCESSING;
        $securityCode = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabc0';

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventHistoryItemMock->expects($this->atLeastOnce())
            ->method('getEventData')
            ->willReturn($eventData);
        $eventHistoryItemMock->expects($this->atLeastOnce())
            ->method('getReferenceId')
            ->willReturn($referenceId);
        $eventHistoryItemMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn(false);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                [EventQueueInterface::REFERENCE_ID, $referenceId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $newEventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($eventId)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setReferenceId')
            ->with($referenceId)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventData')
            ->with($eventData)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setSecurityCode')
            ->with($securityCode)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with($newEventStatus)
            ->willReturnSelf();
        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newEventQueueItemMock);

        $this->codeGeneratorMock->expects($this->once())
            ->method('getCode')
            ->willReturn($securityCode);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($newEventQueueItemMock)
            ->willReturn($newEventQueueItemMock);

        $this->assertEquals($newEventQueueItemMock, $this->model->add($eventMock, $eventHistoryItemMock));
    }

    /**
     * Test add method if an error occurs on save event queue item
     */
    public function testAddErrorOnSave()
    {
        $storeId = 1;
        $eventId = 2;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $email = 'test@example.com';
        $eventData = json_encode(
            [
                'store_id' => $storeId,
                'email' => $email,
            ]
        );
        $referenceId = 10;
        $newEventStatus = EventQueueInterface::STATUS_PROCESSING;
        $securityCode = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabc0';

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventHistoryItemMock->expects($this->atLeastOnce())
            ->method('getEventData')
            ->willReturn($eventData);
        $eventHistoryItemMock->expects($this->atLeastOnce())
            ->method('getReferenceId')
            ->willReturn($referenceId);
        $eventHistoryItemMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn(false);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                [EventQueueInterface::REFERENCE_ID, $referenceId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $newEventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($eventId)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setReferenceId')
            ->with($referenceId)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setEventData')
            ->with($eventData)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setSecurityCode')
            ->with($securityCode)
            ->willReturnSelf();
        $newEventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with($newEventStatus)
            ->willReturnSelf();
        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newEventQueueItemMock);

        $this->codeGeneratorMock->expects($this->once())
            ->method('getCode')
            ->willReturn($securityCode);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($newEventQueueItemMock)
            ->willThrowException(new CouldNotSaveException(__('Unknown error!')));

        $this->assertFalse($this->model->add($eventMock, $eventHistoryItemMock));
    }

    /**
     * Test add method if there are sent emails
     */
    public function testAddIfHaveAlreadySentEmails()
    {
        $storeId = 1;
        $eventId = 2;
        $email = 'test@example.com';
        $eventData = json_encode(
            [
                'store_id' => $storeId,
                'email' => $email,
            ]
        );
        $referenceId = 10;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventHistoryItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn($eventData);
        $eventHistoryItemMock->expects($this->once())
            ->method('getReferenceId')
            ->willReturn($referenceId);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn(false);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::EVENT_ID, $eventId, 'eq'],
                [EventQueueInterface::REFERENCE_ID, $referenceId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$eventQueueEmailMock]);
        $eventQueueSearchResultMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventQueueSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventQueueItemMock]);
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultMock);

        $this->assertFalse($this->model->add($eventMock, $eventHistoryItemMock));
    }

    /**
     * Test add method if there are sent emails but duplicates check is disabled
     */
    public function testAddIfHaveAlreadySentEmailsCheckDuplicatesDisabled()
    {
        $storeId = 1;
        $eventId = 2;
        $eventType = EventInterface::TYPE_WISHLIST_CONTENT_CHANGED;
        $email = 'test@example.com';
        $eventData = json_encode(
            [
                'store_id' => $storeId,
                'email' => $email,
            ]
        );
        $referenceId = 10;
        $eventStatus = EventQueueInterface::STATUS_PROCESSING;
        $securityCode = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabc0';

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventHistoryItemMock->expects($this->atLeastOnce())
            ->method('getEventData')
            ->willReturn($eventData);
        $eventHistoryItemMock->expects($this->once())
            ->method('getReferenceId')
            ->willReturn($referenceId);
        $eventHistoryItemMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn(false);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($eventId)
            ->willReturnSelf();
        $eventQueueItemMock->expects($this->once())
            ->method('setReferenceId')
            ->with($referenceId)
            ->willReturnSelf();
        $eventQueueItemMock->expects($this->once())
            ->method('setEventType')
            ->with($eventType)
            ->willReturnSelf();
        $eventQueueItemMock->expects($this->once())
            ->method('setEventData')
            ->with($eventData)
            ->willReturnSelf();
        $eventQueueItemMock->expects($this->once())
            ->method('setSecurityCode')
            ->with($securityCode)
            ->willReturnSelf();
        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with($eventStatus)
            ->willReturnSelf();
        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueItemMock);

        $this->codeGeneratorMock->expects($this->once())
            ->method('getCode')
            ->willReturn($securityCode);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertEquals(
            $eventQueueItemMock,
            $this->model->add($eventMock, $eventHistoryItemMock, false)
        );
    }

    /**
     * Test add method if the email is unsubscribed
     */
    public function testAddEmailUnsubscribed()
    {
        $storeId = 1;
        $eventId = 2;
        $email = 'test@example.com';
        $eventData = json_encode(
            [
                'store_id' => $storeId,
                'email' => $email,
            ]
        );

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventHistoryItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn($eventData);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn(true);

        $this->assertFalse($this->model->add($eventMock, $eventHistoryItemMock));
    }

    /**
     * Test processUnprocessedItems method
     */
    public function testProcessUnprocessedItems()
    {
        $page = 1;
        $maxItemCount = 3;

        $eventQueueItemOneMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemTwoMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItems = [$eventQueueItemOneMock, $eventQueueItemTwoMock];

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with($maxItemCount)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setCurrentPage')
            ->with($page)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueSearchResultsMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultsMock);

        $eventQueueSearchResultsMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($eventQueueItems);
        $eventQueueSearchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(count($eventQueueItems));

        $this->eventQueueItemProcessorMock->expects($this->exactly(count($eventQueueItems)))
            ->method('process')
            ->withConsecutive([$eventQueueItemOneMock], [$eventQueueItemTwoMock])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->assertTrue($this->model->processUnprocessedItems($maxItemCount));
    }

    /**
     * Test processUnprocessedItems method if there are too many unprocessed items
     */
    public function testProcessUnprocessedItemsTooManyItems()
    {
        $page = 1;
        $maxItemCount = 2;

        $eventQueueItemOneMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemTwoMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemThreeMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItems = [$eventQueueItemOneMock, $eventQueueItemTwoMock, $eventQueueItemThreeMock];

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(EventQueueInterface::STATUS, EventQueueInterface::STATUS_PROCESSING)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with($maxItemCount)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setCurrentPage')
            ->with($page)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventQueueSearchResultsMock = $this->getMockBuilder(EventQueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventQueueSearchResultsMock);

        $eventQueueSearchResultsMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($eventQueueItems);
        $eventQueueSearchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(count($eventQueueItems));

        $this->eventQueueItemProcessorMock->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive([$eventQueueItemOneMock], [$eventQueueItemTwoMock])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->assertTrue($this->model->processUnprocessedItems($maxItemCount));
    }
}
