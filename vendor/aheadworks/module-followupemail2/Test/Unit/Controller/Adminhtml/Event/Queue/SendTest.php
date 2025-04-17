<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Send;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Cancel
 */
class SendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cancel
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

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

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->eventQueueManagementMock = $this->getMockBuilder(EventQueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            Send::class,
            [
                'context' => $this->contextMock,
                'eventQueueManagement' => $this->eventQueueManagementMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $eventQueueId = 1;

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($eventQueueId);

        $this->eventQueueManagementMock->expects($this->once())
            ->method('sendNextScheduledEmail')
            ->with($eventQueueId)
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Email was successfully sent.'))
            ->willReturnSelf();

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method if an error occurs
     */
    public function testExecuteException()
    {
        $eventQueueId = 1;
        $errorMessage = __('Error!');

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($eventQueueId);

        $this->eventQueueManagementMock->expects($this->once())
            ->method('sendNextScheduledEmail')
            ->with($eventQueueId)
            ->willThrowException(new LocalizedException($errorMessage));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage)
            ->willReturnSelf();

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no id specified
     */
    public function testExecuteNoId()
    {
        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn(null);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
