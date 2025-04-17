<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Cart;

use Aheadworks\Followupemail2\Controller\Cart\Restore;
use Aheadworks\Followupemail2\Model\Event\Queue\CartRestorer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Cart\Restore
 */
class RestoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Restore
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var CartRestorer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRestorerMock;

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

        $this->cartRestorerMock = $this->getMockBuilder(CartRestorer::class)
            ->setMethods(['restore'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Restore::class,
            [
                'context' => $this->contextMock,
                'cartRestorer' => $this->cartRestorerMock,
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

        $this->cartRestorerMock->expects($this->once())
            ->method('restore')
            ->with($securityCode)
            ->willReturn(true);

        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
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

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Wrong code specified'))
            ->willReturnSelf();

        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method with restore exception
     */
    public function testExecuteException()
    {
        $securityCode = 'K3joCAvXO3BxzIWYNF3rhn5xQjHMsRF8';
        $exceptionMessage = __('Unknow error occurs!');

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

        $this->cartRestorerMock->expects($this->once())
            ->method('restore')
            ->with($securityCode)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }
}
