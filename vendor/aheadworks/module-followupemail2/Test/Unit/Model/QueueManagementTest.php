<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterface;
use Aheadworks\Followupemail2\Model\QueueManagement;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterfaceFactory;
use Aheadworks\Followupemail2\Model\Sender;
use Aheadworks\Followupemail2\Model\Email\ContentResolver as EmailContentResolver;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\QueueManagement
 */
class QueueManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueManagement
     */
    private $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var QueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepositoryMock;

    /**
     * @var QueueInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueFactoryMock;

    /**
     * @var PreviewInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $previewFactoryMock;

    /**
     * @var Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderMock;

    /**
     * EmailContentResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailContentResolverMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

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
     * @var array
     */
    private $emailData = [
        'event_id' => 1,
        'event_type' => EventInterface::TYPE_ABANDONED_CART,
        'email_id' => 10,
        'contentIds' => [
            EmailInterface::CONTENT_VERSION_A => 1,
            EmailInterface::CONTENT_VERSION_B => 2
        ]
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->queueFactoryMock = $this->getMockBuilder(QueueInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->previewFactoryMock = $this->getMockBuilder(PreviewInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->senderMock = $this->getMockBuilder(Sender::class)
            ->setMethods(['sendQueueItem', 'sendTestEmail', 'renderEventQueueItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailContentResolverMock = $this->getMockBuilder(EmailContentResolver::class)
            ->setMethods(['getCurrentContent', 'getCurrentAbContentVersion'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->setMethods(['date', 'timestamp'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            QueueManagement::class,
            [
                'config' => $this->configMock,
                'eventRepository' => $this->eventRepositoryMock,
                'emailRepository' => $this->emailRepositoryMock,
                'queueRepository' => $this->queueRepositoryMock,
                'queueFactory' => $this->queueFactoryMock,
                'previewFactory' => $this->previewFactoryMock,
                'sender' => $this->senderMock,
                'emailContentResolver' => $this->emailContentResolverMock,
                'dateTime' => $this->dateTimeMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'logger' => $this->loggerMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test send method
     */
    public function testSend()
    {
        $sentTime = '2017-01-01 00:00:00';

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_SENT)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSentAt')
            ->with($sentTime)
            ->willReturnSelf();

        $this->senderMock->expects($this->once())
            ->method('sendQueueItem')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->willReturn($sentTime);

        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->assertTrue($this->model->send($queueItemMock));
    }

    /**
     * Test send method if an error occurs
     */
    public function testSendException()
    {
        $errorMessage = 'Error!';

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_FAILED)
            ->willReturnSelf();

        $this->senderMock->expects($this->once())
            ->method('sendQueueItem')
            ->with($queueItemMock)
            ->willThrowException(new MailException(__($errorMessage)));

        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($errorMessage)
            ->willReturn(null);

        $this->assertFalse($this->model->send($queueItemMock));
    }

    /**
     * Test send method if an error on queue item saving occurs
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email failed to save.
     */
    public function testSendQueueSavingException()
    {
        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_FAILED)
            ->willReturnSelf();

        $this->senderMock->expects($this->once())
            ->method('sendQueueItem')
            ->with($queueItemMock)
            ->willThrowException(new MailException(__('Error!')));

        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willThrowException(new CouldNotSaveException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->model->send($queueItemMock);
    }

    /**
     * Test sendById method
     */
    public function testSendById()
    {
        $queueId = 10;
        $sentTime = '2017-01-01 00:00:00';

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_SENT)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSentAt')
            ->with($sentTime)
            ->willReturnSelf();

        $this->queueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($queueId)
            ->willReturn($queueItemMock);

        $this->senderMock->expects($this->once())
            ->method('sendQueueItem')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->willReturn($sentTime);

        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->assertTrue($this->model->sendById($queueId));
    }

    /**
     * Test sendById method if no queue item with id specified
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testSendByIdException()
    {
        $queueId = 10;

        $this->queueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($queueId)
            ->willThrowException(NoSuchEntityException::singleField('id', $queueId));
        $this->expectException(LocalizedException::class);
        $this->model->sendById($queueId);
    }

    /**
     * Test getPreview method
     */
    public function testGetPreview()
    {
        $storeId = 1;
        $senderEmail = 'sender@example.com';
        $senderName = 'Sender';
        $recipientEmail = 'recipient@example.com';
        $recipientName = 'Recipient';
        $subject = 'Test subject';
        $content = 'Test content';

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $queueItemMock->expects($this->once())
            ->method('getSenderName')
            ->willReturn($senderName);
        $queueItemMock->expects($this->once())
            ->method('getSenderEmail')
            ->willReturn($senderEmail);
        $queueItemMock->expects($this->once())
            ->method('getRecipientName')
            ->willReturn($recipientName);
        $queueItemMock->expects($this->once())
            ->method('getRecipientEmail')
            ->willReturn($recipientEmail);
        $queueItemMock->expects($this->once())
            ->method('getSubject')
            ->willReturn($subject);
        $queueItemMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();
        $previewMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSenderName')
            ->with($senderName)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSenderEmail')
            ->with($senderEmail)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setRecipientName')
            ->with($recipientName)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setRecipientEmail')
            ->with($recipientEmail)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSubject')
            ->with($subject)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setContent')
            ->with($content)
            ->willReturnSelf();
        $this->previewFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($previewMock);

        $this->assertEquals($previewMock, $this->model->getPreview($queueItemMock));
    }

    /**
     * Test sendTest method
     *
     * @param EmailInterface $emailMock
     * @param int $contentVersion
     * @param bool $abTestEnabled
     * @param bool $sendSuccess
     * @param bool $result
     * @dataProvider sendTestDataProvider
     */
    public function testSendTest($emailMock, $contentVersion, $abTestEnabled, $sendSuccess, $result)
    {
        $storeId = 1;
        $errorMessage = 'Error!';
        $sentTime = '2017-01-01 00:00:00';

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->emailData['event_id']);
        $eventMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($this->emailData['event_type']);
        $eventMock->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([$storeId]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->emailData['event_id'])
            ->willReturn($eventMock);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($this->emailData['event_id'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventType')
            ->with($this->emailData['event_type'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventEmailId')
            ->with($this->emailData['email_id'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEmailContentId')
            ->with($this->emailData['contentIds'][$contentVersion])
            ->willReturnSelf();
        if ($abTestEnabled) {
            $queueItemMock->expects($this->once())
                ->method('setContentVersion')
                ->with($contentVersion)
                ->willReturnSelf();
        }
        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueItemMock);

        if ($sendSuccess) {
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_SENT)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setScheduledAt')
                ->with($sentTime)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSentAt')
                ->with($sentTime)
                ->willReturnSelf();
            $this->senderMock->expects($this->once())
                ->method('sendTestEmail')
                ->willReturn($queueItemMock);

            $this->dateTimeMock->expects($this->once())
                ->method('date')
                ->willReturn($sentTime);
        } else {
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_FAILED)
                ->willReturnSelf();
            $this->senderMock->expects($this->once())
                ->method('sendTestEmail')
                ->willThrowException(new MailException(__($errorMessage)));

            $this->loggerMock->expects($this->once())
                ->method('warning')
                ->with($errorMessage)
                ->willReturn(null);
        }
        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->assertEquals($result, $this->model->sendTest($emailMock, $contentVersion));
    }

    /**
     * Test sendTest method
     *
     * @param EmailInterface $emailMock
     * @param int $contentVersion
     * @param bool $abTestEnabled
     * @param bool $sendSuccess
     * @dataProvider sendTestDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The email failed to save.
     */
    public function testSendTestQueueSavingException(
        $emailMock,
        $contentVersion,
        $abTestEnabled,
        $sendSuccess
    ) {
        $storeId = 1;
        $errorMessage = 'Error!';
        $sentTime = '2017-01-01 00:00:00';

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->emailData['event_id']);
        $eventMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($this->emailData['event_type']);
        $eventMock->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([$storeId]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->emailData['event_id'])
            ->willReturn($eventMock);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($this->emailData['event_id'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventType')
            ->with($this->emailData['event_type'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventEmailId')
            ->with($this->emailData['email_id'])
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEmailContentId')
            ->with($this->emailData['contentIds'][$contentVersion])
            ->willReturnSelf();
        if ($abTestEnabled) {
            $queueItemMock->expects($this->once())
                ->method('setContentVersion')
                ->with($contentVersion)
                ->willReturnSelf();
        }
        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueItemMock);

        if ($sendSuccess) {
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_SENT)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setScheduledAt')
                ->with($sentTime)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSentAt')
                ->with($sentTime)
                ->willReturnSelf();
            $this->senderMock->expects($this->once())
                ->method('sendTestEmail')
                ->willReturn($queueItemMock);

            $this->dateTimeMock->expects($this->once())
                ->method('date')
                ->willReturn($sentTime);
        } else {
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_FAILED)
                ->willReturnSelf();
            $this->senderMock->expects($this->once())
                ->method('sendTestEmail')
                ->willThrowException(new MailException(__($errorMessage)));

            $this->loggerMock->expects($this->once())
                ->method('warning')
                ->with($errorMessage)
                ->willReturn(null);
        }
        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willThrowException(new CouldNotSaveException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->model->sendTest($emailMock, $contentVersion);
    }

    /**
     * @return array
     */
    public function sendTestDataProvider()
    {
        return [
            [
                $this->getSendTestEmailMock($this->emailData['email_id'], $this->emailData['event_id'], 1),
                EmailInterface::CONTENT_VERSION_A,
                true,
                true,
                true
            ],
            [
                $this->getSendTestEmailMock($this->emailData['email_id'], $this->emailData['event_id'], 1),
                EmailInterface::CONTENT_VERSION_B,
                true,
                true,
                true
            ],
            [
                $this->getSendTestEmailMock($this->emailData['email_id'], $this->emailData['event_id'], 0),
                EmailInterface::CONTENT_VERSION_A,
                false,
                true,
                true
            ],
            [
                $this->getSendTestEmailMock($this->emailData['email_id'], $this->emailData['event_id'], 1),
                EmailInterface::CONTENT_VERSION_A,
                false,
                false,
                false
            ],
        ];
    }

    /**
     * Get email mock for send test
     *
     * @param $eventId
     * @param $emailId
     * @param $abTestingMode
     * @return EmailInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSendTestEmailMock($emailId, $eventId, $abTestingMode)
    {
        $aContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $aContentMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->emailData['contentIds'][EmailInterface::CONTENT_VERSION_A]);
        $bContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $bContentMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->emailData['contentIds'][EmailInterface::CONTENT_VERSION_B]);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getId')
            ->willReturn($emailId);
        $emailMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $emailMock->expects($this->once())
            ->method('getAbTestingMode')
            ->willReturn($abTestingMode);
        $emailMock->expects($this->once())
            ->method('getContent')
            ->willReturn([$aContentMock, $bContentMock]);

        return $emailMock;
    }

    /**
     * Test schedule method
     *
     * @param EmailInterface $emailMock
     * @param bool $configSender
     * @param bool $sendSuccess
     * @param bool $result
     * @dataProvider scheduleDataProvider
     */
    public function testSchedule($emailMock, $configSender, $sendSuccess, $result)
    {
        $storeId = 1;
        $eventQueueEmailId = 10;
        $eventId = 5;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventData = [
            'store_id' => $storeId
        ];
        $senderEmail = 'sender@example.com';
        $senderName = 'Sender';
        $recipientEmail = 'recipient@example.com';
        $recipientName = 'Recipient';
        $subject = 'Test subject';
        $content = 'Test content';
        $renderedEmail = [
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'content' => $content
        ];
        $scheduledTime = '2017-01-01 00:00:00';
        $errorMessage = 'Error!';

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($eventData))
            ->willReturn($eventData);
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn(json_encode($eventData));

        $emailContentMock = $this->getMockForAbstractClass(EmailContentInterface::class);
        $this->emailContentResolverMock->expects($this->once())
            ->method('getCurrentContent')
            ->with($emailMock)
            ->willReturn($emailContentMock);

        if ($sendSuccess) {
            $eventQueueItemMock->expects($this->once())
                ->method('getEventId')
                ->willReturn($eventId);
            $eventQueueItemMock->expects($this->once())
                ->method('getEventType')
                ->willReturn($eventType);

            $this->emailContentResolverMock->expects($this->once())
                ->method('getCurrentAbContentVersion')
                ->with($emailMock)
                ->willReturn(EmailInterface::CONTENT_VERSION_A);

            $queueItemMock = $this->getMockBuilder(QueueInterface::class)
                ->getMockForAbstractClass();
            $queueItemMock->expects($this->once())
                ->method('setEventId')
                ->with($eventId)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setEventType')
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setEventEmailId')
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setEmailContentId')
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setEventQueueEmailId')
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSenderEmail')
                ->with($senderEmail)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSenderName')
                ->with($senderName)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setRecipientEmail')
                ->with($recipientEmail)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setRecipientName')
                ->with($recipientName)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSubject')
                ->with($subject)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setContent')
                ->with($content)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setStoreId')
                ->with($storeId)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_PENDING)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setScheduledAt')
                ->with($scheduledTime)
                ->willReturnSelf();
            $this->queueFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($queueItemMock);

            $this->senderMock->expects($this->once())
                ->method('renderEventQueueItem')
                ->with($eventQueueItemMock, $emailContentMock)
                ->willReturn($renderedEmail);

            $this->dateTimeMock->expects($this->once())
                ->method('date')
                ->willReturn($scheduledTime);

            if ($configSender) {
                $this->configMock->expects($this->once())
                    ->method('getSenderEmail')
                    ->with($storeId)
                    ->willReturn($senderEmail);
                $this->configMock->expects($this->once())
                    ->method('getSenderName')
                    ->with($storeId)
                    ->willReturn($senderName);
            }
            $this->queueRepositoryMock->expects($this->once())
                ->method('save')
                ->with($queueItemMock)
                ->willReturn($queueItemMock);
            $this->emailRepositoryMock->expects($this->any())
                ->method('save')
                ->with($emailMock)
                ->willReturn($emailMock);
        } else {
            $this->senderMock->expects($this->once())
                ->method('renderEventQueueItem')
                ->willThrowException(new \Exception($errorMessage));

            $this->loggerMock->expects($this->once())
                ->method('warning')
                ->with($errorMessage)
                ->willReturn(null);
        }

        $this->assertEquals($result, $this->model->schedule($eventQueueItemMock, $emailMock, $eventQueueEmailId));
    }

    /**
     * Test schedule method
     */
    public function testScheduleQueueSavingException()
    {
        $emailMock = $this->getScheduleEmailMock(
            $this->emailData['email_id'],
            $this->emailData['event_id'],
            1,
            EmailInterface::CONTENT_VERSION_A
        );
        $configSender = true;
        $storeId = 1;
        $eventQueueEmailId = 10;
        $eventId = 5;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventData = [
            'store_id' => $storeId
        ];
        $senderEmail = 'sender@example.com';
        $senderName = 'Sender';
        $recipientEmail = 'recipient@example.com';
        $recipientName = 'Recipient';
        $subject = 'Test subject';
        $content = 'Test content';
        $renderedEmail = [
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'content' => $content
        ];
        $scheduledTime = '2017-01-01 00:00:00';
        $errorMessage = 'The email failed to save.';

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($eventData))
            ->willReturn($eventData);
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn(json_encode($eventData));

        $eventQueueItemMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $eventQueueItemMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);

        $emailContentMock = $this->getMockForAbstractClass(EmailContentInterface::class);
        $this->emailContentResolverMock->expects($this->once())
            ->method('getCurrentContent')
            ->with($emailMock)
            ->willReturn($emailContentMock);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemMock->expects($this->once())
            ->method('setEventId')
            ->with($eventId)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventType')
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventEmailId')
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEmailContentId')
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setEventQueueEmailId')
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSenderEmail')
            ->with($senderEmail)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSenderName')
            ->with($senderName)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setRecipientEmail')
            ->with($recipientEmail)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setRecipientName')
            ->with($recipientName)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSubject')
            ->with($subject)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setContent')
            ->with($content)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_PENDING)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setScheduledAt')
            ->with($scheduledTime)
            ->willReturnSelf();
        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueItemMock);

        $this->senderMock->expects($this->once())
            ->method('renderEventQueueItem')
            ->with($eventQueueItemMock, $emailContentMock)
            ->willReturn($renderedEmail);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->willReturn($scheduledTime);

        if ($configSender) {
            $this->configMock->expects($this->once())
                ->method('getSenderEmail')
                ->with($storeId)
                ->willReturn($senderEmail);
            $this->configMock->expects($this->once())
                ->method('getSenderName')
                ->with($storeId)
                ->willReturn($senderName);
        }
        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willThrowException(new CouldNotSaveException(__('Error!')));
        $this->emailRepositoryMock->expects($this->never())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($errorMessage)
            ->willReturn(null);

        $this->assertEquals(false, $this->model->schedule($eventQueueItemMock, $emailMock, $eventQueueEmailId));
    }

    /**
     * @return array
     */
    public function scheduleDataProvider()
    {
        return [
            [
                $this->getScheduleEmailMock(
                    $this->emailData['email_id'],
                    $this->emailData['event_id'],
                    1,
                    EmailInterface::CONTENT_VERSION_A
                ),
                true,
                true,
                true
            ],
            [
                $this->getScheduleEmailMock(
                    $this->emailData['email_id'],
                    $this->emailData['event_id'],
                    1,
                    EmailInterface::CONTENT_VERSION_A
                ),
                false,
                false,
                false
            ]
        ];
    }

    /**
     * Get email mock for schedule
     *
     * @param $emailId
     * @param $eventId
     * @param $abTestingMode
     * @return EmailInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getScheduleEmailMock($emailId, $eventId, $abTestingMode, $primaryEmailContent)
    {
        $aContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $aContentMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->emailData['contentIds'][EmailInterface::CONTENT_VERSION_A]);
        $bContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $bContentMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->emailData['contentIds'][EmailInterface::CONTENT_VERSION_B]);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getId')
            ->willReturn($emailId);
        $emailMock->expects($this->any())
            ->method('getAbTestingMode')
            ->willReturn($abTestingMode);
        $emailMock->expects($this->any())
            ->method('getPrimaryEmailContent')
            ->willReturn($primaryEmailContent);

        return $emailMock;
    }

    /**
     * Test sendScheduledEmails method
     */
    public function testSendScheduledEmails()
    {
        $currentTimestamp = 1483228800;
        $emailsCount = 1;

        $this->dateTimeMock->expects($this->once())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [QueueInterface::STATUS, QueueInterface::STATUS_PENDING, 'eq'],
                [
                    QueueInterface::SCHEDULED_AT,
                    date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT, $currentTimestamp),
                    'lteq'
                ]
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock = $this->getMockBuilder(QueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$queueItemMock]);

        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($queueSearchResultsMock);

        $sentTime = '2017-01-01 00:00:00';

        $queueItemMock->expects($this->once())
            ->method('setStatus')
            ->with(QueueInterface::STATUS_SENT)
            ->willReturnSelf();
        $queueItemMock->expects($this->once())
            ->method('setSentAt')
            ->with($sentTime)
            ->willReturnSelf();

        $this->senderMock->expects($this->once())
            ->method('sendQueueItem')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->willReturn($sentTime);

        $this->queueRepositoryMock->expects($this->once())
            ->method('save')
            ->with($queueItemMock)
            ->willReturn($queueItemMock);

        $this->assertTrue($this->model->sendScheduledEmails($emailsCount));
    }

    /**
     * Test cancelByEventQueueEmailId method
     *
     * @param array $queueItems
     * @param bool $shouldBeSaved
     * @dataProvider cancelByEventQueueEmailIdDataProvider
     */
    public function testCancelByEventQueueEmailId($queueItems, $shouldBeSaved)
    {
        $eventQueueEmailId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(QueueInterface::EVENT_QUEUE_EMAIL_ID, $eventQueueEmailId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $queueSearchResultsMock = $this->getMockBuilder(QueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($queueItems);

        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($queueSearchResultsMock);

        if ($shouldBeSaved) {
            $this->queueRepositoryMock->expects($this->once())
                ->method('save')
                ->with(end($queueItems))
                ->willReturn(end($queueItems));
        }

        $this->assertTrue($this->model->cancelByEventQueueEmailId($eventQueueEmailId));
    }

    /**
     * @return array
     */
    public function cancelByEventQueueEmailIdDataProvider()
    {
        $queueItemSentMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemSentMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(QueueInterface::STATUS_SENT);

        $queueItemPendingMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemPendingMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(QueueInterface::STATUS_PENDING);
        $queueItemPendingMock->expects($this->once())
            ->method('setStatus')
            ->willReturn(QueueInterface::STATUS_CANCELLED);

        return [
            [
                'queueItems' => [], 'shouldBeSaved' => false
            ],
            [
                'queueItems' => [$queueItemSentMock], 'shouldBeSaved' => false
            ],
            [
                'queueItems' => [$queueItemPendingMock], 'shouldBeSaved' => true
            ],
        ];
    }

    /**
     * Test sendByEventQueueEmailId method
     *
     * @param array $queueItems
     * @param bool $shouldBeSend
     * @dataProvider sendByEventQueueEmailIdDataProvider
     */
    public function testSendByEventQueueEmailId($queueItems, $shouldBeSend)
    {
        $eventQueueEmailId = 1;
        $storeId = 1;
        $sentTime = '2017-01-01 00:00:00';

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(QueueInterface::EVENT_QUEUE_EMAIL_ID, $eventQueueEmailId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $queueSearchResultsMock = $this->getMockBuilder(QueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($queueItems);

        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($queueSearchResultsMock);

        if ($shouldBeSend) {
            $queueItemMock = end($queueItems);
            $queueItemMock->expects($this->once())
                ->method('getStoreId')
                ->willReturn($storeId);
            $queueItemMock->expects($this->once())
                ->method('setStatus')
                ->with(QueueInterface::STATUS_SENT)
                ->willReturnSelf();
            $queueItemMock->expects($this->once())
                ->method('setSentAt')
                ->with($sentTime)
                ->willReturnSelf();

            $this->senderMock->expects($this->once())
                ->method('sendQueueItem')
                ->with($queueItemMock)
                ->willReturn($queueItemMock);

            $this->dateTimeMock->expects($this->once())
                ->method('date')
                ->willReturn($sentTime);

            $this->queueRepositoryMock->expects($this->once())
                ->method('save')
                ->with($queueItemMock)
                ->willReturn($queueItemMock);
        }

        $this->assertTrue($this->model->sendByEventQueueEmailId($eventQueueEmailId));
    }

    /**
     * @return array
     */
    public function sendByEventQueueEmailIdDataProvider()
    {
        $queueItemSentMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemSentMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(QueueInterface::STATUS_SENT);

        $queueItemPendingMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueItemPendingMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(QueueInterface::STATUS_PENDING);
        $queueItemPendingMock->expects($this->once())
            ->method('setStatus')
            ->willReturn(QueueInterface::STATUS_SENT);

        return [
            ['queueItems' => [], 'shouldBeSend' => false],
            ['queueItems' => [$queueItemSentMock], 'shouldBeSend' => false],
            ['queueItems' => [$queueItemPendingMock], 'shouldBeSend' => true],
        ];
    }

    /**
     * Test clearQueue method
     */
    public function testClearQueue()
    {
        $keepForDays = 60;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(QueueInterface::SCHEDULED_AT, $this->greaterThan(0), 'lteq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock = $this->getMockBuilder(QueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$queueItemMock]);

        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($queueSearchResultsMock);
        $this->queueRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($queueItemMock)
            ->willReturn(true);

        $this->assertTrue($this->model->clearQueue($keepForDays));
    }

    /**
     * Test clearQueue method if clear queue is disabled
     */
    public function testClearQueueNoParam()
    {
        $keepForDays = 0;
        $this->assertFalse($this->model->clearQueue($keepForDays));
    }
}
