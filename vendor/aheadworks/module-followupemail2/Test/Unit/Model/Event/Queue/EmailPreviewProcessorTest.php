<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueEmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\EmailPreviewProcessor;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterfaceFactory;
use Aheadworks\Followupemail2\Model\Email\ContentResolver as EmailContentResolver;
use Aheadworks\Followupemail2\Model\Sender;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\EmailPreviewProcessor
 */
class EmailPreviewProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailPreviewProcessor
     */
    private $model;

    /**
     * @var QueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepositoryMock;

    /**
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var PreviewInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $previewFactoryMock;

    /**
     * @var EmailContentResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailContentResolverMock;

    /**
     * @var Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

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

        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->previewFactoryMock = $this->getMockBuilder(PreviewInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailContentResolverMock = $this->getMockBuilder(EmailContentResolver::class)
            ->setMethods(['getCurrentContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->senderMock = $this->getMockBuilder(Sender::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            EmailPreviewProcessor::class,
            [
                'queueRepository' => $this->queueRepositoryMock,
                'queueManagement' => $this->queueManagementMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'previewFactory' => $this->previewFactoryMock,
                'emailContentResolver' => $this->emailContentResolverMock,
                'sender' => $this->senderMock,
                'config' => $this->configMock,
                'logger' => $this->loggerMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test getCreatedEmailPreview method
     *
     * @param array $items
     * @param PreviewInterface|false $result
     * @dataProvider getCreatedEmailPreviewDataProvider
     */
    public function testGetCreatedEmailPreview($items, $result)
    {
        $eventQueueEmailId = 1;
        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueEmailId);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(QueueInterface::EVENT_QUEUE_EMAIL_ID, $eventQueueEmailId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $queueSearchResults = $this->getMockBuilder(QueueSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $queueSearchResults->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($queueSearchResults);

        $this->queueManagementMock->expects($this->any())
            ->method('getPreview')
            ->willReturn($result);

        $this->assertSame($result, $this->model->getCreatedEmailPreview($eventQueueEmailMock));
    }

    /**
     * @return array
     */
    public function getCreatedEmailPreviewDataProvider()
    {
        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();

        return [
            ['items' => [$queueItemMock], 'result' => $previewMock],
            ['items' => [], 'result' => false],
        ];
    }

    /**
     * Test getCreatedEmailPreview method if an error occurs
     */
    public function testGetCreatedEmailPreviewError()
    {
        $eventQueueEmailId = 1;
        $eventQueueEmailMock = $this->getMockBuilder(EventQueueEmailInterface::class)
            ->getMockForAbstractClass();
        $eventQueueEmailMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueEmailId);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(QueueInterface::EVENT_QUEUE_EMAIL_ID, $eventQueueEmailId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->queueRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willThrowException(new NoSuchEntityException());

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('No such entity.')
            ->willReturn(null);

        $this->assertFalse($this->model->getCreatedEmailPreview($eventQueueEmailMock));
    }

    /**
     * Test getScheduledEmailPreview method
     */
    public function testGetScheduledEmailPreview()
    {
        $storeId = 1;
        $eventData = [
            'store_id' => $storeId
        ];
        $senderName = 'Sender';
        $senderEmail = 'sender@example.com';
        $recipientName = 'Recipient';
        $recipientEmail = 'recipient@eexample.com';
        $subject = 'Subject';
        $content = 'Content';
        $renderedData = [
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'content' => $content,
        ];

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn(json_encode($eventData));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($eventData))
            ->willReturn($eventData);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $this->emailContentResolverMock->expects($this->once())
            ->method('getCurrentContent')
            ->with($emailMock)
            ->willReturn($emailContentMock);

        $this->senderMock->expects($this->once())
            ->method('renderEventQueueItem')
            ->with($eventQueueItemMock, $emailContentMock)
            ->willReturn($renderedData);
        $this->configMock->expects($this->once())
            ->method('getSenderName')
            ->with($storeId)
            ->willReturn($senderName);
        $this->configMock->expects($this->once())
            ->method('getSenderEmail')
            ->with($storeId)
            ->willReturn($senderEmail);

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

        $this->assertSame($previewMock, $this->model->getScheduledEmailPreview($eventQueueItemMock, $emailMock));
    }

    /**
     * Test getScheduledEmailPreview method if an error occurs
     */
    public function testGetScheduledEmailPreviewError()
    {
        $storeId = 1;
        $eventData = [
            'store_id' => $storeId
        ];

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getEventData')
            ->willReturn(json_encode($eventData));
        $this->serializerMock->expects($this->once())
             ->method('unserialize')
             ->with(json_encode($eventData))
             ->willReturn($eventData);
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $this->emailContentResolverMock->expects($this->once())
            ->method('getCurrentContent')
            ->with($emailMock)
            ->willReturn($emailContentMock);

        $this->senderMock->expects($this->once())
            ->method('renderEventQueueItem')
            ->with($eventQueueItemMock, $emailContentMock)
            ->willThrowException(new \Exception('Error!'));

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Error!')
            ->willReturn(null);

        $this->assertFalse($this->model->getScheduledEmailPreview($eventQueueItemMock, $emailMock));
    }
}
