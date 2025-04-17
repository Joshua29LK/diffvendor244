<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel\Indexer\ScheduledEmails;

use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailProcessor as EventQueueEmailProcessor;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\Processor as ScheduledEmailsProcessor;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var ScheduledEmailsProcessor
     */
    private $model;

    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventQueueEmailProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueEmailProcessorMock;

    /**
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeFactoryMock;

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

        $this->campaignRepositoryMock = $this->getMockBuilder(CampaignRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueEmailProcessorMock = $this->getMockBuilder(EventQueueEmailProcessor::class)
            ->setMethods(['getNextNotSentEmail', 'getLastEmailSentDate', 'isLastScheduledEmailPending'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            ScheduledEmailsProcessor::class,
            [
                'campaignRepository' => $this->campaignRepositoryMock,
                'eventRepository' => $this->eventRepositoryMock,
                'eventQueueEmailProcessor' => $this->eventQueueEmailProcessorMock,
                'emailManagement' => $this->emailManagementMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test getData method
     */
    public function testGetData()
    {
        $eventQueueItemId = 100;
        $eventQueueEmails = [];
        $eventId = 25;
        $campaignId = 10;
        $campaignName = 'Test Campaign';
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventName = 'Abandoned Cart';
        $emailName = 'Test Email';
        $emailAbTestMode = 1;
        $emailWhen = EmailInterface::WHEN_AFTER;
        $emailDays = 1;
        $emailHours = 2;
        $emailMinutes = 3;
        $lastEmailSentDate = '2018-01-01 00:00:00';
        $scheduledTo = '2018-01-01 02:00:00';

        $storeId = 1;
        $customerName = 'Test Customer';
        $email = 'test@example.com';
        $eventData = json_encode([
            'customer_name' => $customerName,
            'email' => $email,
            'store_id' => $storeId,
        ]);

        $result = [
            'event_queue_id' => $eventQueueItemId,
            'campaign_name' => $campaignName,
            'event_name' => $eventName,
            'event_type' => $eventType,
            'email_name' => $emailName,
            'ab_testing_mode' => $emailAbTestMode,
            'recipient_name' => $customerName,
            'recipient_email' => $email,
            'store_id' => $storeId,
            'scheduled_to' => $scheduledTo,
        ];

        $eventQueueItemMock = $this->getEventQueueItemMock(
            $eventQueueItemId,
            $eventId,
            $eventType,
            $eventData,
            $eventQueueEmails
        );

        $eventMock = $this->getEventMock($campaignId, $eventName);
        $this->eventRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $campaignMock = $this->getCampaignMock($campaignName);
        $this->campaignRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($campaignId)
            ->willReturn($campaignMock);

        $emailMock = $this->getEmailMock(
            $emailName,
            $emailAbTestMode,
            $emailWhen,
            $emailDays,
            $emailHours,
            $emailMinutes
        );
        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('getNextNotSentEmail')
            ->with($eventQueueItemMock)
            ->willReturn($emailMock);
        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('isLastScheduledEmailPending')
            ->with($eventQueueEmails)
            ->willReturn(false);
        $this->eventQueueEmailProcessorMock->expects($this->once())
            ->method('getLastEmailSentDate')
            ->with($eventQueueItemMock)
            ->willReturn($lastEmailSentDate);

        $dateTimeMock = $this->getMockBuilder(\DateTime::class)
            ->setMethods(['add', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $dateTimeMock->expects($this->once())
            ->method('add')
            ->willReturnSelf();
        $dateTimeMock->expects($this->once())
            ->method('format')
            ->with(DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($scheduledTo);
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->with($lastEmailSentDate, new \DateTimeZone('UTC'))
            ->willReturn($dateTimeMock);

        $this->assertEquals($result, $this->model->getData($eventQueueItemMock));
    }

    /**
     * Get event queue item mock
     *
     * @param int $eventQueueItemId
     * @param int $eventId
     * @param string $eventType
     * @param array $eventData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEventQueueItemMock($eventQueueItemId, $eventId, $eventType, $eventData, $emails)
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueItemId);
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventType')
            ->willReturn($eventType);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($eventData)
            ->willReturn(json_decode($eventData, true));
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEventData')
            ->willReturn($eventData);
        $eventQueueItemMock->expects($this->atLeastOnce())
            ->method('getEmails')
            ->willReturn($emails);
        return $eventQueueItemMock;
    }

    /**
     * Get event mock
     *
     * @param int $campaignId
     * @param string $eventName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEventMock($campaignId, $eventName)
    {
        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->any())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $eventMock->expects($this->any())
            ->method('getName')
            ->willReturn($eventName);
        return $eventMock;
    }

    /**
     * get campaign mock
     *
     * @param string $campaignName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getCampaignMock($campaignName)
    {
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($campaignName);
        return $campaignMock;
    }

    /**
     * Get email mock
     *
     * @param string $emailName
     * @param int $abTestMode
     * @param int $when
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEmailMock($emailName, $abTestMode, $when, $days, $hours, $minutes)
    {
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getName')
            ->willReturn($emailName);
        $emailMock->expects($this->any())
            ->method('getAbTestingMode')
            ->willReturn($abTestMode);
        $emailMock->expects($this->any())
            ->method('getWhen')
            ->willReturn($when);
        $emailMock->expects($this->any())
            ->method('getEmailSendDays')
            ->willReturn($days);
        $emailMock->expects($this->any())
            ->method('getEmailSendHours')
            ->willReturn($hours);
        $emailMock->expects($this->any())
            ->method('getEmailSendMinutes')
            ->willReturn($minutes);
        return $emailMock;
    }

    /**
     * Test getCampaignName method
     */
    public function testGetCampaignName()
    {
        $campaignId = 1;
        $campaignName = 'Test Campaign';
        $eventId = 2;
        $eventName = 'Does not matter';

        $eventMock = $this->getEventMock($campaignId, $eventName);
        $this->eventRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $campaignMock = $this->getCampaignMock($campaignName);
        $this->campaignRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($campaignId)
            ->willReturn($campaignMock);

        $this->assertEquals(
            $campaignName,
            $this->invokeMethod($this->model, 'getCampaignName', [$eventId])
        );
    }

    /**
     * Test getCampaignName method if an error occurs
     */
    public function testGetCampaignNameError()
    {
        $campaignId = 1;
        $campaignName = '';
        $eventId = 1;
        $eventName = 'Does not matter';

        $eventMock = $this->getEventMock($campaignId, $eventName);
        $this->eventRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $this->campaignRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($campaignId)
            ->willThrowException(new NoSuchEntityException(__('No such entity.')));

        $this->assertEquals(
            $campaignName,
            $this->invokeMethod($this->model, 'getCampaignName', [$eventId])
        );
    }

    /**
     * Test getEventName method
     */
    public function testGetEventName()
    {
        $campaignId = 1;
        $eventId = 2;
        $eventName = 'Test Event';

        $eventMock = $this->getEventMock($campaignId, $eventName);
        $this->eventRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $this->assertEquals(
            $eventName,
            $this->invokeMethod($this->model, 'getEventName', [$eventId])
        );
    }

    /**
     * Test getEventName method if an error occurs
     */
    public function testGetEventNameError()
    {
        $eventId = 1;
        $eventName = '';

        $this->eventRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventId)
            ->willThrowException(new NoSuchEntityException(__('No such entity.')));

        $this->assertEquals(
            $eventName,
            $this->invokeMethod($this->model, 'getEventName', [$eventId])
        );
    }

    /**
     * Test getScheduledTo method
     * @param array $eventQueueEmails
     * @param bool $isLastScheduledEmailPending
     * @param bool $whenAfter
     * @param string $email
     * @param string $result
     * @throws \ReflectionException
     * @dataProvider getScheduledToDataProvider
     */
    public function testGetScheduledTo(
        $eventQueueEmails,
        $isLastScheduledEmailPending,
        $whenAfter,
        $email,
        $result
    ) {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->any())
            ->method('getEmails')
            ->willReturn($eventQueueEmails);

        $this->eventQueueEmailProcessorMock->expects($this->any())
            ->method('isLastScheduledEmailPending')
            ->with($eventQueueEmails)
            ->willReturn($isLastScheduledEmailPending);

        if (!$isLastScheduledEmailPending) {
            $dateTimeMock = $this->getMockBuilder(\DateTime::class)
                ->setMethods(['add', 'format'])
                ->disableOriginalConstructor()
                ->getMock();
            $dateTimeMock->expects($this->once())
                ->method('format')
                ->with(DateTime::DATETIME_PHP_FORMAT)
                ->willReturn($result);
            if ($whenAfter) {
                $dateTimeMock->expects($this->once())
                    ->method('add')
                    ->willReturnSelf();
            }

            $this->dateTimeFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($dateTimeMock);
        }

        $this->assertEquals(
            $result,
            $this->invokeMethod($this->model, 'getScheduledTo', [$eventQueueItemMock, $email])
        );
    }

    /**
     * @return array
     */
    public function getScheduledToDataProvider()
    {
        $emailName = 'Test Email';
        $abTestMode = 1;
        $days = 1;
        $hours = 2;
        $minutes = 3;
        $whenAfter = EmailInterface::WHEN_AFTER;
        $whenBefore = EmailInterface::WHEN_BEFORE;

        $eventQueueEmailUpdatedAt = '2018-01-01 00:00:00';

        $newEmailSendDate = '2018-01-02 02:03:00';

        $eventQueueEmailMock = $this->getEventQueueEmailMock($eventQueueEmailUpdatedAt);
        $emailSendAfterMock = $this->getEmailMock($emailName, $abTestMode, $whenAfter, $days, $hours, $minutes);
        $emailSendBeforeMock = $this->getEmailMock($emailName, $abTestMode, $whenBefore, $days, $hours, $minutes);

        return [
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'isLastScheduledEmailPending' => true,
                'whenAfter' => true,
                'email' => false,
                'result' => null
            ],
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'isLastScheduledEmailPending' => true,
                'whenAfter' => true,
                'email' => $emailSendAfterMock,
                'result' => $eventQueueEmailUpdatedAt
            ],
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'isLastScheduledEmailPending' => false,
                'whenAfter' => true,
                'email' => $emailSendAfterMock,
                'result' => $newEmailSendDate
            ],
            [
                'eventQueueEmails' => [$eventQueueEmailMock],
                'isLastScheduledEmailPending' => false,
                'whenAfter' => false,
                'email' => $emailSendBeforeMock,
                'result' => '2018-01-01 00:00:00'
            ],
        ];
    }

    /**
     * Test getScheduledTo method if an exception occurs
     */
    public function testGetScheduledToException()
    {
        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        $this->eventQueueEmailProcessorMock->expects($this->any())
            ->method('isLastScheduledEmailPending')
            ->willThrowException(new \Exception('Error!'));

        $this->assertEquals(
            null,
            $this->invokeMethod($this->model, 'getScheduledTo', [$eventQueueItemMock, $emailMock])
        );
    }

    /**
     * Test getEmailInterval method
     */
    public function testGetEmailInterval()
    {
        $emailName = 'Test Email';
        $abTestMode = 1;
        $when = EmailInterface::WHEN_AFTER;
        $days = 1;
        $hours = 2;
        $minutes = 3;

        $result = new \DateInterval('P1DT2H3M');

        $emailMock = $this->getEmailMock($emailName, $abTestMode, $when, $days, $hours, $minutes);

        $this->assertEquals(
            $result,
            $this->invokeMethod($this->model, 'getEmailInterval', [$emailMock])
        );
    }

    /**
     * Get event queue email mock
     *
     * @param string $eventQueueEmailUpdatedAt
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEventQueueEmailMock($eventQueueEmailUpdatedAt)
    {
        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->any())
            ->method('getUpdatedAt')
            ->willReturn($eventQueueEmailUpdatedAt);
        return $eventQueueEmailMock;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
