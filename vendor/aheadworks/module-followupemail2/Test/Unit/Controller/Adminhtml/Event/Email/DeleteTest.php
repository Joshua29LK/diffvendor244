<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\Delete;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ResponseDataProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\Delete
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
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

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

        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->responseDataProcessorMock = $this->getMockBuilder(ResponseDataProcessor::class)
            ->setMethods(['getPreparedData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'emailRepository' => $this->emailRepositoryMock,
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
        $eventId = 2;
        $emailId = 10;

        $resultData = [
            'emails'     => [],
            'totals'     => [],
            'events_count' => 0,
            'emails_count' => 0,
            'campaign_stats' => []
        ];

        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'emails'     => [],
            'totals'     => [],
            'events_count' => 0,
            'emails_count' => 0,
            'campaign_stats' => []
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn($emailId);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->atLeastOnce())
            ->method('getEventId')
            ->willReturn($eventId);
        $this->emailRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);

        $this->emailRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($emailId)
            ->willReturn(true);

        $this->responseDataProcessorMock->expects($this->once())
            ->method('getPreparedData')
            ->with($eventId)
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
     * Test execute method when no email id specified
     */
    public function testExecuteNoIdSpecified()
    {
        $requestParam = 'id';
        $result =  [
            'error'     => true,
            'message'   => __('Email Id is not specified!')
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
     * Test execute method when no email with specified id
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

        $this->emailRepositoryMock->expects($this->atLeastOnce())
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
