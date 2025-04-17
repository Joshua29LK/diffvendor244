<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Queue\Delete;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue\Delete
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delete
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
     * @var QueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepositoryMock;

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

        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'queueRepository' => $this->queueRepositoryMock
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

        $this->queueRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($queueId)
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Email was successfully deleted.'))
            ->willReturnSelf();

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when the queue item no exists
     */
    public function testExecuteNoQueueItemException()
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

        $this->queueRepositoryMock->expects($this->once())
            ->method('deleteById')
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
