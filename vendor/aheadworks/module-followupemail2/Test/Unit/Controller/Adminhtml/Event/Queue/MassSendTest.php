<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\MassSend;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\Collection as ScheduledEmailsCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\CollectionFactory
    as ScheduledEmailsCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Test for \Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\MassSend
 */
class MassSendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MassSend
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var ScheduledEmailsCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsCollectionFactoryMock;

    /**
     * @var EventQueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduledEmailsCollectionFactoryMock = $this->getMockBuilder(ScheduledEmailsCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueManagementMock = $this->getMockBuilder(EventQueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            MassSend::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'scheduledEmailsCollectionFactory' => $this->scheduledEmailsCollectionFactoryMock,
                'eventQueueManagement' => $this->eventQueueManagementMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $eventQueueIds = [1, 2];
        $eventQueueCount = 2;

        $collectionMock = $this->getMockBuilder(ScheduledEmailsCollection::class)
            ->setMethods(['getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($eventQueueIds);
        $this->scheduledEmailsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->eventQueueManagementMock->expects($this->atLeastOnce())
            ->method('sendNextScheduledEmail')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 email(s) have been sent.', $eventQueueCount))
            ->willReturnSelf();

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method if not all emails are sent
     */
    public function testExecuteNotAllSent()
    {
        $eventQueueIds = [1, 2];
        $eventQueueCount = 1;

        $collectionMock = $this->getMockBuilder(ScheduledEmailsCollection::class)
            ->setMethods(['getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($eventQueueIds);
        $this->scheduledEmailsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->eventQueueManagementMock->expects($this->exactly(2))
            ->method('sendNextScheduledEmail')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls(true, false);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 email(s) have been sent.', $eventQueueCount))
            ->willReturnSelf();

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when an exception occurs
     */
    public function testExecuteWithException()
    {
        $eventQueueIds = [1, 2];
        $errorMessage = __('Error!');

        $collectionMock = $this->getMockBuilder(ScheduledEmailsCollection::class)
            ->setMethods(['getSize', 'getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($eventQueueIds);
        $this->scheduledEmailsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->eventQueueManagementMock->expects($this->atLeastOnce())
            ->method('sendNextScheduledEmail')
            ->willThrowException(new LocalizedException($errorMessage));

        $this->messageManagerMock->expects($this->exactly(2))
            ->method('addErrorMessage')
            ->withConsecutive([$errorMessage], [__('None of selected emails can be sent.')])
            ->willReturnSelf();

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
