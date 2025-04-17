<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Queue\Send;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue\Send
 */
class SendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Send
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
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

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

        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            Send::class,
            [
                'context' => $this->contextMock,
                'queueManagement' => $this->queueManagementMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $queueId = 1;

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
            ->willReturn($queueId);

        $this->queueManagementMock->expects($this->once())
            ->method('sendById')
            ->with($queueId)
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
     * Test execute method when the queue item can not be sent
     */
    public function testExecuteCanNotBeSent()
    {
        $queueId = 1;

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
            ->willReturn($queueId);

        $this->queueManagementMock->expects($this->once())
            ->method('sendById')
            ->with($queueId)
            ->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This email can not be sent.'))
            ->willReturnSelf();

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when an exception occurs
     */
    public function testExecuteException()
    {
        $queueId = 1;

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
            ->willReturn($queueId);

        $this->queueManagementMock->expects($this->once())
            ->method('sendById')
            ->with($queueId)
            ->willThrowException(new NoSuchEntityException());

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('No such entity.'))
            ->willReturnSelf();

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no queue id specified
     */
    public function testExecuteNoQueueId()
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
