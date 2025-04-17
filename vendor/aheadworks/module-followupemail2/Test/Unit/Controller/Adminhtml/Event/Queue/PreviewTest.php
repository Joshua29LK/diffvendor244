<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview\ResponseDataProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;

/**
 * Test for \Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview
 */
class PreviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Preview
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
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var EventQueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueRepositoryMock;

    /**
     * @var ResponseDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseDataProcessorMock;

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

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
            ]
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueRepositoryMock = $this->getMockBuilder(EventQueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->responseDataProcessorMock = $this->getMockBuilder(ResponseDataProcessor::class)
            ->setMethods(['getPreparedData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Preview::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'eventQueueRepository' => $this->eventQueueRepositoryMock,
                'responseDataProcessor' => $this->responseDataProcessorMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $requestParam = 'id';
        $eventQueueId = 1;

        $resultData = [
            'preview' => '<p>Rendered preview</p>',
        ];

        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'preview' => '<p>Rendered preview</p>',
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn($eventQueueId);

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($eventQueueId)
            ->willReturn($eventQueueItemMock);

        $this->responseDataProcessorMock->expects($this->once())
            ->method('getPreparedData')
            ->with($eventQueueItemMock)
            ->willReturn($resultData);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no id specified
     */
    public function testExecuteNoIdSpecified()
    {
        $requestParam = 'id';
        $result =  [
            'error'     => true,
            'message'   => __('Id is not specified!')
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn(null);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no event queue id with specified id
     */
    public function testExecuteWithExcepton()
    {
        $requestParam = 'id';
        $requestParamValue = 10;
        $result =  [
            'error'     => true,
            'message'   => __('No such entity.')
        ];
        $exception = new NoSuchEntityException();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn($requestParamValue);

        $this->eventQueueRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($requestParamValue)
            ->willThrowException($exception);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }
}
