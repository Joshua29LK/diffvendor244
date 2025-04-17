<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Unsubscribe;

use Aheadworks\Followupemail2\Controller\Unsubscribe\All;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Unsubscribe\All
 */
class AllTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var All
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

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

        $this->messageManagerMock =  $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            All::class,
            [
                'context' => $this->contextMock,
                'eventManagement' => $this->eventManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $securityCode = 'K3joCAvXO3BxzIWYNF3rhn5xQjHMsRF8';

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('code')
            ->willReturn($securityCode);

        $this->eventManagementMock->expects($this->once())
            ->method('unsubscribeFromAll')
            ->with($securityCode)
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You have been successfully unsubscribed.'))
            ->willReturnSelf();

        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no code specified
     */
    public function testExecuteNoCode()
    {
        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('code')
            ->willReturn(null);

        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }
}
