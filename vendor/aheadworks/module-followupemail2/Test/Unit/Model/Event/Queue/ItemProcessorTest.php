<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\ItemProcessor as EventQueueItemProcessor;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\Validator as EventQueueValidator;
use Aheadworks\Followupemail2\Model\Serializer;
use Aheadworks\Followupemail2\Model\Unsubscribe\Service as UnsubscribeService;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor as EventQueueEmailProcessor;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailScheduler as EventQueueEmailScheduler;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailPreviewProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\ItemProcessor
 */
class ItemProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventQueueItemProcessor
     */
    private $model;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventQueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueRepositoryMock;

    /**
     * @var EventQueueValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueValidatorMock;

    /**
     * @var UnsubscribeService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeServiceMock;

    /**
     * @var EventQueueEmailProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailProcessorMock;

    /**
     * @var EventQueueEmailScheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailSchedulerMock;

    /**
     * @var EmailPreviewProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailPreviewProcessorMock;

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

        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueRepositoryMock = $this->getMockBuilder(EventQueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueValidatorMock = $this->getMockBuilder(EventQueueValidator::class)
            ->setMethods(['isEventValid'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->unsubscribeServiceMock = $this->getMockBuilder(UnsubscribeService::class)
            ->setMethods(['isUnsubscribed'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueEmailProcessorMock = $this->getMockBuilder(EventQueueEmailProcessor::class)
            ->setMethods([
                'getLastScheduledEmail',
                'isPending',
                'isEventQueueItemShouldBeCancelled',
                'process',
                'cancelNextEmail',
                'getNextEmail'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailPreviewProcessorMock = $this->getMockBuilder(EmailPreviewProcessor::class)
            ->setMethods(['getCreatedEmailPreview', 'getScheduledEmailPreview'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueEmailSchedulerMock = $this->getMockBuilder(EventQueueEmailScheduler::class)
            ->setMethods(['sendScheduledEmail', 'scheduleNextEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            EventQueueItemProcessor::class,
            [
                'eventRepository' => $this->eventRepositoryMock,
                'eventQueueRepository' => $this->eventQueueRepositoryMock,
                'eventQueueValidator' => $this->eventQueueValidatorMock,
                'unsubscribeService' => $this->unsubscribeServiceMock,
                'eventQueueEmailProcessor' => $this->eventQueueEmailProcessorMock,
                'eventQueueEmailScheduler' => $this->eventQueueEmailSchedulerMock,
                'emailPreviewProcessor' => $this->emailPreviewProcessorMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test process method
     *
     * @param EventQueueEmailInterface $lastScheduledEmail
     * @param bool $isPending
     * @param bool $shouldBeCancelled
     * @param EventQueueEmailInterface[] $oldEmails
     * @param EventQueueEmailInterface[] $newEmails
     * @param bool $result
     * @dataProvider processDataProvider
     */
    public function testProcess($lastScheduledEmail, $isPending, $shouldBeCancelled, $oldEmails, $newEmails, $result)
    {
        $eventId = 1;
        $eventValid = true;
        $storeId = 2;
        $email = 'test@example.com';
        $eventData = json_encode([
            'store_id' => $storeId,
            'email' => $email,
        ]);
        $isUnsubscribed = false;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn($eventData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn($isUnsubscribed);

        $this->eventQueueValidatorMock->expects($this->once())
            ->method('isEventValid')
            ->with($eventMock)
            ->willReturn($eventValid);

        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('getLastScheduledEmail')
            ->with($eventQueueItemMock)
            ->willReturn($lastScheduledEmail);

        if ($lastScheduledEmail) {
            $this->eventQueueEmailProcessorMock->expects($this->once())
                ->method('isPending')
                ->with($lastScheduledEmail)
                ->willReturn($isPending);

            if ($shouldBeCancelled) {
                $this->eventQueueEmailProcessorMock->expects($this->once())
                    ->method('isEventQueueItemShouldBeCancelled')
                    ->with($eventMock, $lastScheduledEmail)
                    ->willReturn($shouldBeCancelled);

                $eventQueueItemMock->expects($this->once())
                    ->method('setStatus')
                    ->with(EventQueueInterface::STATUS_CANCELLED)
                    ->willReturnSelf();

                $this->eventQueueRepositoryMock->expects($this->once())
                    ->method('save')
                    ->with($eventQueueItemMock)
                    ->willReturn($eventQueueItemMock);
            }

            if (!$isPending && !$shouldBeCancelled) {
                $eventQueueItemMock->expects($this->exactly(2))
                    ->method('getEmails')
                    ->willReturnOnConsecutiveCalls($oldEmails, $newEmails);
                $this->eventQueueEmailProcessorMock->expects($this->once())
                    ->method('process')
                    ->with($eventQueueItemMock)
                    ->willReturn($eventQueueItemMock);
                $this->eventQueueRepositoryMock->expects($this->once())
                    ->method('save')
                    ->with($eventQueueItemMock)
                    ->willReturn($eventQueueItemMock);
            }
        }

        $this->assertEquals($result, $this->model->process($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $lastScheduledEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $newScheduledEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'lastScheduledEmail' => $lastScheduledEmailMock,
                'isPending' => true,
                'shouldBeCancelled' => false,
                'oldEmails' => [$lastScheduledEmailMock],
                'newEmails' => [$lastScheduledEmailMock],
                'result' => false
            ],
            [
                'lastScheduledEmail' => $lastScheduledEmailMock,
                'isPending' => false,
                'shouldBeCancelled' => true,
                'oldEmails' => [$lastScheduledEmailMock],
                'newEmails' => [$lastScheduledEmailMock],
                'result' => false
            ],
            [
                'lastScheduledEmail' => $lastScheduledEmailMock,
                'isPending' => false,
                'shouldBeCancelled' => false,
                'oldEmails' => [$lastScheduledEmailMock],
                'newEmails' => [$lastScheduledEmailMock],
                'result' => false
            ],
            [
                'lastScheduledEmail' => $lastScheduledEmailMock,
                'isPending' => false,
                'shouldBeCancelled' => false,
                'oldEmails' => [$lastScheduledEmailMock],
                'newEmails' => [$lastScheduledEmailMock, $newScheduledEmailMock],
                'result' => true
            ],
        ];
    }

    /**
     * Test process method if an event not valid
     */
    public function testProcessNoValidEvent()
    {
        $eventId = 1;
        $eventValid = false;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);

        $this->eventQueueValidatorMock->expects($this->once())
            ->method('isEventValid')
            ->with($eventMock)
            ->willReturn($eventValid);

        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueInterface::STATUS_CANCELLED)
            ->willReturnSelf();

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertFalse($this->model->process($eventQueueItemMock));
    }

    /**
     * Test process method if an event can not be loaded
     */
    public function testProcessNoEventException()
    {
        $eventId = 1;

        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willThrowException(new NoSuchEntityException(__('No such entity with id=1')));

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueItemMock)
            ->willReturn(true);

        $this->assertFalse($this->model->process($eventQueueItemMock));
    }

    /**
     * Test process method if an email is unsubscribed
     */
    public function testProcessUnsubscibed()
    {
        $eventId = 1;
        $eventValid = true;
        $storeId = 2;
        $email = 'test@example.com';
        $eventData = json_encode([
            'store_id' => $storeId,
            'email' => $email,
        ]);
        $isUnsubscribed = true;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn($eventData);

        $this->unsubscribeServiceMock->expects($this->once())
            ->method('isUnsubscribed')
            ->with($eventId, $email, $storeId)
            ->willReturn($isUnsubscribed);

        $this->eventQueueValidatorMock->expects($this->once())
            ->method('isEventValid')
            ->with($eventMock)
            ->willReturn($eventValid);

        $eventQueueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(EventQueueInterface::STATUS_CANCELLED)
            ->willReturnSelf();

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertFalse($this->model->process($eventQueueItemMock));
    }

    /**
     * Test cancelScheduledEmail method
     * @param bool $shouldBeCancelled
     * @param bool $noMoreEmailsToSchedule
     * @dataProvider cancelScheduledEmailDataProvider
     */
    public function testCancelScheduledEmail($shouldBeCancelled, $noMoreEmailsToSchedule)
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($eventMock);

        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('cancelNextEmail')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('getLastScheduledEmail')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueEmailMock);

        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('isEventQueueItemShouldBeCancelled')
            ->with($eventMock, $eventQueueEmailMock)
            ->willReturn($shouldBeCancelled);

        if ($shouldBeCancelled || $noMoreEmailsToSchedule) {
            $eventQueueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(EventQueueInterface::STATUS_CANCELLED)
                ->willReturnSelf();
        }

        if ($noMoreEmailsToSchedule) {
            $this->eventQueueEmailProcessorMock->expects($this->any())
                ->method('getNextEmail')
                ->with($eventQueueItemMock)
                ->willReturn(false);
        } else {
            $emailMock = $this->getMockBuilder(EmailInterface::class)
                ->getMockForAbstractClass();
            $this->eventQueueEmailProcessorMock->expects($this->any())
                ->method('getNextEmail')
                ->with($eventQueueItemMock)
                ->willReturn($emailMock);
        }

        $this->eventQueueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventQueueItemMock)
            ->willReturn($eventQueueItemMock);

        $this->assertSame($eventQueueItemMock, $this->model->cancelScheduledEmail($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function cancelScheduledEmailDataProvider()
    {
        return [
            ['shouldBeCancelled' => true, 'noMoreEmailsToSchedule' => false],
            ['shouldBeCancelled' => false, 'noMoreEmailsToSchedule' => false],
            ['shouldBeCancelled' => true, 'noMoreEmailsToSchedule' => true],
            ['shouldBeCancelled' => false, 'noMoreEmailsToSchedule' => true],
        ];
    }

    /**
     * Test cancelScheduledEmail method if an exception occurs
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testCancelScheduledEmailException()
    {
        $exceptionMessage = __('No such entity with id = 1');

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException($exceptionMessage));
        $this->expectException(NoSuchEntityException::class);
        $this->model->cancelScheduledEmail($eventQueueItemMock);
    }

    /**
     * Test sendNextScheduledEmail method
     *
     * @param EventQueueEmailInterface $lastEventEmail
     * @param bool $lastEventEmailPending
     * @param EmailInterface $nextEmail
     * @param bool $lastEmail
     * @dataProvider sendNextScheduledEmailDataProvider
     */
    public function testSendNextScheduledEmail($lastEventEmail, $lastEventEmailPending, $nextEmail, $lastEmail)
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        if ($lastEventEmail && $lastEventEmailPending) {
            $this->eventQueueEmailProcessorMock->expects($this->once())
                ->method('getLastScheduledEmail')
                ->with($eventQueueItemMock)
                ->willReturn($lastEventEmail);

            $this->eventQueueEmailSchedulerMock->expects($this->once())
                ->method('sendScheduledEmail')
                ->with($lastEventEmail)
                ->willReturnSelf();

            if ($nextEmail) {
                $this->eventQueueEmailProcessorMock->expects($this->once())
                    ->method('getNextEmail')
                    ->with($eventQueueItemMock)
                    ->willReturn($nextEmail);
            } else {
                $eventQueueItemMock->expects($this->once())
                    ->method('setStatus')
                    ->with(EventQueueInterface::STATUS_FINISHED)
                    ->willReturnSelf();
            }
        } elseif ($nextEmail) {
            if ($lastEmail) {
                $this->eventQueueEmailProcessorMock->expects($this->exactly(2))
                    ->method('getNextEmail')
                    ->withConsecutive([$eventQueueItemMock], [$eventQueueItemMock])
                    ->willReturn($nextEmail, false);

                $eventQueueItemMock->expects($this->once())
                    ->method('setStatus')
                    ->with(EventQueueInterface::STATUS_FINISHED)
                    ->willReturnSelf();
            } else {
                $this->eventQueueEmailProcessorMock->expects($this->exactly(2))
                    ->method('getNextEmail')
                    ->with($eventQueueItemMock)
                    ->willReturn($nextEmail);
            }

            $this->eventQueueEmailSchedulerMock->expects($this->once())
                ->method('scheduleNextEmail')
                ->with($eventQueueItemMock, $nextEmail)
                ->willReturn($eventQueueItemMock);

            $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
                ->getMockForAbstractClass();

            $this->eventQueueEmailProcessorMock->expects($this->exactly(2))
                ->method('getLastScheduledEmail')
                ->withConsecutive([$eventQueueItemMock], [$eventQueueItemMock])
                ->willReturnOnConsecutiveCalls($lastEventEmail, $eventQueueEmailMock);

            $this->eventQueueEmailSchedulerMock->expects($this->once())
                ->method('sendScheduledEmail')
                ->with($eventQueueEmailMock)
                ->willReturnSelf();
        } else {
            $this->eventQueueEmailProcessorMock->expects($this->once())
                ->method('getLastScheduledEmail')
                ->with($eventQueueItemMock)
                ->willReturn($lastEventEmail);
        }

        $this->assertSame($eventQueueItemMock, $this->model->sendNextScheduledEmail($eventQueueItemMock));
    }

    /**
     * @return array
     */
    public function sendNextScheduledEmailDataProvider()
    {
        $eventQueueEmailSentMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailSentMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EventQueueEmailInterface::STATUS_SENT);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_PENDING),
                'lastEventEmailPending' => true,
                'nextEmail' => false,
                'lastEmail' => true
            ],
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_PENDING),
                'lastEventEmailPending' => true,
                'nextEmail' => $emailMock,
                'lastEmail' => false
            ],
            [
                'lastEventEmail' => false,
                'lastEventEmailPending' => false,
                'nextEmail' => false,
                'lastEmail' => true
            ],
            [
                'lastEventEmail' => false,
                'lastEventEmailPending' => false,
                'nextEmail' => $emailMock,
                'lastEmail' => false
            ],
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT),
                'lastEventEmailPending' => false,
                'nextEmail' => $emailMock,
                'lastEmail' => false
            ],
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT),
                'lastEventEmailPending' => false,
                'nextEmail' => $emailMock,
                'lastEmail' => true
            ],
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_FAILED),
                'lastEventEmailPending' => false,
                'nextEmail' => $emailMock,
                'lastEmail' => false
            ],
            [
                'lastEventEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_CANCELLED),
                'lastEventEmailPending' => false,
                'nextEmail' => $emailMock,
                'lastEmail' => false
            ],
        ];
    }

    /**
     * Get event queue email mock
     *
     * @param int $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventQueueEmailMock($status)
    {
        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        return $eventQueueEmailMock;
    }

    /**
     * Test getScheduledEmailPreview method
     *
     * @param EventQueueEmailInterface $lastScheduledEmail
     * @param bool $lastScheduledPending
     * @param EmailInterface $nextEmail
     * @param PreviewInterface|false $preview
     * @dataProvider getScheduledEmailPreviewDataProvider
     */
    public function testGetScheduledEmailPreview($lastScheduledEmail, $lastScheduledPending, $nextEmail, $preview)
    {
        $eventQueueItem = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('getLastScheduledEmail')
            ->with($eventQueueItem)
            ->willReturn($lastScheduledEmail);

        if ($lastScheduledPending) {
            $this->emailPreviewProcessorMock->expects($this->once())
                ->method('getCreatedEmailPreview')
                ->with($lastScheduledEmail)
                ->willReturn($preview);
        } else {
            $this->eventQueueEmailProcessorMock->expects($this->once())
                ->method('getNextEmail')
                ->with($eventQueueItem)
                ->willReturn($nextEmail);

            if ($nextEmail) {
                $this->emailPreviewProcessorMock->expects($this->once())
                    ->method('getScheduledEmailPreview')
                    ->with($eventQueueItem, $nextEmail)
                    ->willReturn($preview);
            }
        }

        $this->assertEquals($preview, $this->model->getScheduledEmailPreview($eventQueueItem));
    }

    /**
     * @return array
     */
    public function getScheduledEmailPreviewDataProvider()
    {
        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        return [
            [
                'lastScheduledEmail' => false,
                'lastScheduledPending' => false,
                'nextEmail' => null,
                'preview' => false,
            ],
            [
                'lastScheduledEmail' => false,
                'lastScheduledPending' => false,
                'nextEmail' => $emailMock,
                'preview' => $previewMock,
            ],
            [
                'lastScheduledEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_PENDING),
                'lastScheduledPending' => true,
                'nextEmail' => null,
                'preview' => $previewMock,
            ],
            [
                'lastScheduledEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT),
                'lastScheduledPending' => false,
                'nextEmail' => null,
                'preview' => false,
            ],
            [
                'lastScheduledEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_SENT),
                'lastScheduledPending' => false,
                'nextEmail' => $emailMock,
                'preview' => $previewMock,
            ],
            [
                'lastScheduledEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_FAILED),
                'lastScheduledPending' => false,
                'nextEmail' => $emailMock,
                'preview' => $previewMock,
            ],
            [
                'lastScheduledEmail' => $this->getEventQueueEmailMock(EventQueueEmailInterface::STATUS_CANCELLED),
                'lastScheduledPending' => false,
                'nextEmail' => $emailMock,
                'preview' => $previewMock,
            ],
        ];
    }
}
