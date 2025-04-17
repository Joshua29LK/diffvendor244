<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor as EventQueueEmailProcessor;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\Validator as EventQueueValidator;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler as EventQueueEmailScheduler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor
 */
class EmailProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventQueueEmailProcessor
     */
    private $model;

    /**
     * @var EventQueueEmailInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailFactoryMock;

    /**
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

    /**
     * @var EventQueueValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueValidatorMock;

    /**
     * @var EventQueueEmailScheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailSchedulerMock;

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

        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueValidatorMock = $this->getMockBuilder(EventQueueValidator::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueEmailSchedulerMock = $this->getMockBuilder(EventQueueEmailScheduler::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EventQueueEmailProcessor::class,
            [
                'eventQueueEmailFactory' => $this->eventQueueEmailFactoryMock,
                'emailManagement' => $this->emailManagementMock,
                'eventQueueValidator' => $this->eventQueueValidatorMock,
                'eventQueueEmailScheduler' => $this->eventQueueEmailSchedulerMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param string $lastSentDate
     * @param EventQueueEmailInterface[] $eventQueueEmails
     * @param EmailInterface $email
     * @param bool $isEmailValid
     * @param EmailInterface|false $nextEmail
     * @dataProvider processDataProvider
     */
    public function testProcess($lastSentDate, $eventQueueEmails, $email, $isEmailValid, $nextEmail)
    {
        $eventId = 1;

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);

        if (count($eventQueueEmails) == 0) {
            $eventQueueItemMock->expects($this->once())
                ->method('getCreatedAt')
                ->willReturn($lastSentDate);
        }

        if (!$isEmailValid) {
            $eventQueueItemMock->expects($this->atLeastOnce())
                ->method('getEmails')
                ->willReturn($eventQueueEmails);

            $this->emailManagementMock->expects($this->once())
                ->method('getNextEmailToSend')
                ->with($eventId, count($eventQueueEmails))
                ->willReturn($email);
        }

        if ($email) {
            $this->eventQueueValidatorMock->expects($this->once())
                ->method('isEmailValidToSend')
                ->with($email, $lastSentDate)
                ->willReturn($isEmailValid);
            if ($isEmailValid) {
                $this->eventQueueEmailSchedulerMock->expects($this->once())
                    ->method('scheduleNextEmail')
                    ->with($eventQueueItemMock, $email)
                    ->willReturn($eventQueueItemMock);

                $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
                    ->getMockForAbstractClass();

                $eventQueueItemMock->expects($this->exactly(3))
                    ->method('getEmails')
                    ->willReturnOnConsecutiveCalls(
                        $eventQueueEmails,
                        $eventQueueEmails,
                        array_merge($eventQueueEmails, [$eventQueueEmailMock])
                    );

                $countOfEventQueueEmails = count($eventQueueEmails);
                $this->emailManagementMock->expects($this->exactly(2))
                    ->method('getNextEmailToSend')
                    ->withConsecutive(
                        [$eventId, $countOfEventQueueEmails],
                        [$eventId, $countOfEventQueueEmails + 1]
                    )
                    ->willReturnOnConsecutiveCalls($email, $nextEmail);

                if (!$nextEmail) {
                    $eventQueueItemMock->expects($this->once())
                        ->method('setStatus')
                        ->with(EventQueueInterface::STATUS_FINISHED)
                        ->willReturnSelf();
                }
            }
        } else {
            $eventQueueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(EventQueueInterface::STATUS_FINISHED)
                ->willReturnSelf();
        }

        $this->assertSame($eventQueueItemMock, $this->model->process($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $lastSentDate = '2018-01-01 00:00:00';

        $queueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $queueEmailMock->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn($lastSentDate);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [$queueEmailMock],
                'email' => $emailMock,
                'isEmailValid' => true,
                'nextEmail' => $emailMock,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [],
                'email' => $emailMock,
                'isEmailValid' => true,
                'nextEmail' => $emailMock,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [$queueEmailMock],
                'email' => $emailMock,
                'isEmailValid' => true,
                'nextEmail' => false,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [],
                'email' => $emailMock,
                'isEmailValid' => true,
                'nextEmail' => false,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [$queueEmailMock],
                'email' => $emailMock,
                'isEmailValid' => false,
                'nextEmail' => $emailMock,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [],
                'email' => $emailMock,
                'isEmailValid' => false,
                'nextEmail' => $emailMock,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [$queueEmailMock],
                'email' => false,
                'isEmailValid' => false,
                'nextEmail' => $emailMock,
            ],
            [
                'lastSentDate' => $lastSentDate,
                'eventQueueEmails' => [],
                'email' => false,
                'isEmailValid' => false,
                'nextEmail' => $emailMock,
            ],
        ];
    }

    /**
     * Test cancelNextEmail method
     *
     * @param EventQueueEmailInterface[] $eventQueueEmails
     * @param bool $lastScheduledEmailPending
     * @dataProvider cancelNextEmailDataProvider
     */
    public function testCancelNextEmail($eventQueueEmails, $lastScheduledEmailPending)
    {
        $eventQueueItemId = 1;

        $eventQueueItem = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItem->expects($this->any())
            ->method('getId')
            ->willReturn($eventQueueItemId);
        $eventQueueItem->expects($this->once())
            ->method('getEmails')
            ->willReturn($eventQueueEmails);

        if ($lastScheduledEmailPending) {
            $this->eventQueueEmailSchedulerMock->expects($this->once())
                ->method('cancelScheduledEmail')
                ->with(end($eventQueueEmails))
                ->willReturnSelf();
            $eventQueueItem->expects($this->once())
                ->method('setEmails')
                ->willReturn($eventQueueEmails);
        } else {
            $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
                ->getMockForAbstractClass();
            $eventQueueEmailMock->expects($this->once())
                ->method('setEventQueueId')
                ->with($eventQueueItemId)
                ->willReturnSelf();
            $eventQueueEmailMock->expects($this->once())
                ->method('setStatus')
                ->with(EventQueueEmailInterface::STATUS_CANCELLED)
                ->willReturnSelf();
            $this->eventQueueEmailFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($eventQueueEmailMock);

            $eventQueueItem->expects($this->once())
                ->method('setEmails')
                ->willReturn(array_merge($eventQueueEmails, [$eventQueueEmailMock]));
        }

        $this->assertSame($eventQueueItem, $this->model->cancelNextEmail($eventQueueItem));
    }

    /**
     * @return array
     */
    public function cancelNextEmailDataProvider()
    {
        $sentEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $sentEmailMock->expects($this->any())
            ->method('getStatus')
            ->willReturn(EventQueueEmailInterface::STATUS_SENT);
        $pendingEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $pendingEmailMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EventQueueEmailInterface::STATUS_PENDING);
        $pendingEmailMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueEmailInterface::STATUS_CANCELLED)
            ->willReturnSelf();

        return [
            [
                'lastScheduledEmail' => [],
                'lastScheduledEmailPending' => false
            ],
            [
                'lastScheduledEmail' => [$sentEmailMock, $sentEmailMock],
                'lastScheduledEmailPending' => false
            ],
            [
                'lastScheduledEmail' => [$sentEmailMock, $pendingEmailMock],
                'lastScheduledEmailPending' => true
            ],
        ];
    }

    /**
     * Test getLastScheduledEmail method
     */
    public function testGetLastScheduledEmail()
    {
        $queueEmailOneMock = $this->getMockBuilder(EventQueueEmailInterface::class)
             ->getMockForAbstractClass();
        $queueEmailTwoMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([$queueEmailOneMock, $queueEmailTwoMock]);

        $this->assertSame($queueEmailTwoMock, $this->model->getLastScheduledEmail($eventQueueItemMock));
    }

    /**
     * Test getLastScheduledEmail method if no emails scheduled
     */
    public function testGetLastScheduledEmailNoEmails()
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEmails')
            ->willReturn([]);

        $this->assertSame(false, $this->model->getLastScheduledEmail($eventQueueItemMock));
    }

    /**
     * Test isPending method
     *
     * @param int $status
     * @param bool $result
     * @dataProvider isPendingDataProvider
     */
    public function testIsPending($status, $result)
    {
        $queueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $queueEmailMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        $this->assertEquals($result, $this->model->isPending($queueEmailMock));
    }

    /**
     * @return array
     */
    public function isPendingDataProvider()
    {
        return [
            ['status' => EventQueueEmailInterface::STATUS_PENDING, 'result' => true],
            ['status' => EventQueueEmailInterface::STATUS_SENT, 'result' => false],
            ['status' => EventQueueEmailInterface::STATUS_FAILED, 'result' => false],
            ['status' => EventQueueEmailInterface::STATUS_CANCELLED, 'result' => false],
        ];
    }

    /**
     * Test isEventQueueItemShouldBeCancelled method
     *
     * @param int $failedEmailsMode
     * @param int $status
     * @param bool $result
     * @dataProvider isEventQueueItemShouldBeCancelledDataProvider
     */
    public function testIsEventQueueItemShouldBeCancelled($failedEmailsMode, $status, $result)
    {
        $eventId = 1;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getFailedEmailsMode')
            ->willReturn($failedEmailsMode);

        $queueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        if ($failedEmailsMode == EventInterface::FAILED_EMAILS_CANCEL) {
            $queueEmailMock->expects($this->atLeastOnce())
                ->method('getStatus')
                ->willReturn($status);
        }

        $this->assertEquals($result, $this->model->isEventQueueItemShouldBeCancelled($eventMock, $queueEmailMock));
    }

    /**
     * @return array
     */
    public function isEventQueueItemShouldBeCancelledDataProvider()
    {
        return [
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_CANCEL,
                'status' => EventQueueEmailInterface::STATUS_PENDING,
                'result' => false
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_CANCEL,
                'status' => EventQueueEmailInterface::STATUS_SENT,
                'result' => false
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_CANCEL,
                'status' => EventQueueEmailInterface::STATUS_FAILED,
                'result' => true
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_CANCEL,
                'status' => EventQueueEmailInterface::STATUS_CANCELLED,
                'result' => true
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_SKIP,
                'status' => EventQueueEmailInterface::STATUS_PENDING,
                'result' => false
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_SKIP,
                'status' => EventQueueEmailInterface::STATUS_SENT,
                'result' => false
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_SKIP,
                'status' => EventQueueEmailInterface::STATUS_FAILED,
                'result' => false
            ],
            [
                'failed_emails_mode' => EventInterface::FAILED_EMAILS_SKIP,
                'status' => EventQueueEmailInterface::STATUS_CANCELLED,
                'result' => false
            ],
        ];
    }

    /**
     * Test getNextNotSentEmail method
     *
     * @param array $eventQueueEmails
     * @param int $nextEmailToSend
     * @param EventQueueInterface $result
     * @dataProvider getNextNotSentEmailDataProvider
     */
    public function testGetNextNotSentEmail($eventQueueEmails, $nextEmailToSend, $result)
    {
        $eventId = 1;

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEmails')
            ->willReturn($eventQueueEmails);

        $this->emailManagementMock->expects($this->once())
            ->method('getNextEmailToSend')
            ->with($eventId, $nextEmailToSend)
            ->willReturn($result);

        $this->assertSame($result, $this->model->getNextNotSentEmail($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function getNextNotSentEmailDataProvider()
    {
        $queueEmailPendingMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_PENDING);
        $queueEmailFailedMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_FAILED);
        $queueEmailCancelledMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_CANCELLED);
        $queueEmailSentMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'eventQueueEmails' => [],
                'nextEmailToSend' => 0,
                'result' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$queueEmailSentMock, $queueEmailSentMock],
                'nextEmailToSend' => 2,
                'result' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$queueEmailSentMock, $queueEmailCancelledMock],
                'nextEmailToSend' => 2,
                'result' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$queueEmailSentMock, $queueEmailFailedMock],
                'nextEmailToSend' => 2,
                'result' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$queueEmailSentMock, $queueEmailPendingMock],
                'nextEmailToSend' => 1,
                'result' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$queueEmailCancelledMock],
                'nextEmailToSend' => 1,
                'result' => false,
            ],
        ];
    }

    /**
     * Get event queue email mock
     *
     * @param int $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEventQueueEmailMock($status)
    {
        $queueEmailPendingMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $queueEmailPendingMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($status);
        return $queueEmailPendingMock;
    }

    /**
     * Test getNextEmail method
     *
     * @param array $eventQueueEmails
     * @param EmailInterface $nextEmailToSend
     * @dataProvider getNextEmailDataProvider
     */
    public function testGetNextEmail($eventQueueEmails, $nextEmailToSend)
    {
        $eventId = 1;

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEmails')
            ->willReturn($eventQueueEmails);

        $this->emailManagementMock->expects($this->once())
            ->method('getNextEmailToSend')
            ->with($eventId, count($eventQueueEmails))
            ->willReturn($nextEmailToSend);

        $this->assertSame($nextEmailToSend, $this->model->getNextEmail($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function getNextEmailDataProvider()
    {
        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'eventQueueEmails' => [],
                'nextEmailToSend' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'nextEmailToSend' => $emailMock,
            ],
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'nextEmailToSend' => false,
            ],
        ];
    }

    /**
     * Test isLastScheduledEmailPending method
     *
     * @param array $eventQueueEmails
     * @param EventQueueInterface $result
     * @dataProvider isLastScheduledEmailPendingDataProvider
     */
    public function testIsLastScheduledEmailPending($eventQueueEmails, $result)
    {
        $this->assertSame($result, $this->model->isLastScheduledEmailPending($eventQueueEmails));
    }

    /**
     * @return array
     */
    public function isLastScheduledEmailPendingDataProvider()
    {
        $queueEmailPendingMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_PENDING);
        $queueEmailFailedMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_FAILED);
        $queueEmailCancelledMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_CANCELLED);
        $queueEmailSentMock = $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT);

        return [
            [
                'eventQueueEmails' => [],
                'result' => false,
            ],
            [
                'eventQueueEmails' => [$queueEmailFailedMock],
                'result' => false,
            ],
            [
                'eventQueueEmails' => [$queueEmailCancelledMock],
                'result' => false,
            ],
            [
                'eventQueueEmails' => [$queueEmailSentMock],
                'result' => false,
            ],
            [
                'eventQueueEmails' => [$queueEmailPendingMock],
                'result' => true,
            ],
        ];
    }
}
