<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler as EventQueueEmailScheduler;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler
 */
class EmailSchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventQueueEmailScheduler
     */
    private $model;

    /**
     * @var EventQueueEmailInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailFactoryMock;

    /**
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

    /**
     * @var EventQueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventQueueEmailFactoryMock = $this->getMockBuilder(EventQueueEmailInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueRepositoryMock = $this->getMockBuilder(EventQueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            EventQueueEmailScheduler::class,
            [
                'eventQueueEmailFactory' => $this->eventQueueEmailFactoryMock,
                'queueManagement' => $this->queueManagementMock,
                'eventQueueRepository' => $this->eventQueueRepositoryMock,
            ]
        );
    }

    /**
     * Test scheduleNextEmail method
     * @param bool $scheduled
     * @dataProvider scheduleNextEmailDataProvider
     */
    public function testScheduleNextEmail($scheduled)
    {
        $eventQueueEmailId = 1;

        $queueEmailOldMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $queueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $queueEmailMock->expects($this->once())
            ->method('getid')
            ->willReturn($eventQueueEmailId);
        $this->eventQueueEmailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueEmailMock);

        $eventQueueItemEmptyMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemEmptyMock->expects($this->atLeastOnce())
            ->method('getEmails')
            ->willReturn([$queueEmailOldMock]);
        $eventQueueItemEmptyMock->expects($this->once())
            ->method('setEmails')
            ->with([$queueEmailOldMock, $queueEmailMock])
            ->willReturnSelf();

        $eventQueueItemSavedMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemSavedMock->expects($this->atLeastOnce())
            ->method('getEmails')
            ->willReturn([$queueEmailOldMock, $queueEmailMock]);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        if ($scheduled) {
            $queueEmailMock->expects($this->once())
                ->method('setStatus')
                ->with(EventQueueEmailInterface::STATUS_PENDING)
                ->willReturnSelf();

            $this->eventQueueRepositoryMock->expects($this->once())
                ->method('save')
                ->with($eventQueueItemEmptyMock)
                ->willReturn($eventQueueItemSavedMock);

            $this->queueManagementMock->expects($this->once())
                ->method('schedule')
                ->with($eventQueueItemSavedMock, $emailMock)
                ->willReturn(true);
        } else {
            $queueEmailMock->expects($this->exactly(2))
                ->method('setStatus')
                ->withConsecutive([EventQueueEmailInterface::STATUS_PENDING], [EventQueueEmailInterface::STATUS_FAILED])
                ->willReturnSelf();

            $this->eventQueueRepositoryMock->expects($this->exactly(2))
                ->method('save')
                ->with($eventQueueItemEmptyMock)
                ->willReturn($eventQueueItemSavedMock);
        }

        $this->assertEquals(
            $eventQueueItemSavedMock,
            $this->model->scheduleNextEmail($eventQueueItemEmptyMock, $emailMock)
        );
    }

    /**
     * @return array
     */
    public function scheduleNextEmailDataProvider()
    {
        return [
            ['scheduled' => true],
            ['scheduled' => false]
        ];
    }

    /**
     * Test cancelScheduledEmail method
     */
    public function testCancelScheduledEmail()
    {
        $eventQueueEmailId = 1;

        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueEmailId);

        $this->queueManagementMock->expects($this->once())
            ->method('cancelByEventQueueEmailId')
            ->with($eventQueueEmailId)
            ->willReturn(true);

        $this->assertSame($this->model, $this->model->cancelScheduledEmail($eventQueueEmailMock));
    }

    /**
     * Test sendScheduledEmail method
     */
    public function testSendScheduledEmail()
    {
        $eventQueueEmailId = 1;

        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueEmailId);

        $this->queueManagementMock->expects($this->once())
            ->method('sendByEventQueueEmailId')
            ->with($eventQueueEmailId)
            ->willReturn(true);

        $this->assertSame($this->model, $this->model->sendScheduledEmail($eventQueueEmailMock));
    }
}
