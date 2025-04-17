<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ChangePosition;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ResponseDataProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\ChangePosition
 */
class ChangePositionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChangePosition
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
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

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
            ->setMethods(['isAjax', 'getPostValue'])
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

        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->responseDataProcessorMock = $this->getMockBuilder(ResponseDataProcessor::class)
            ->setMethods(['getPreparedData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            ChangePosition::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'emailManagement' => $this->emailManagementMock,
                'responseDataProcessor' => $this->responseDataProcessorMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $eventId = 1;
        $emailId = 10;
        $emailPosition = 100;

        $emailPositionsData = [
            EmailInterface::ID => $emailId,
            EmailInterface::POSITION => $emailPosition,
        ];

        $data = [
            'event_id' => $eventId,
            'positions' => [$emailPositionsData],
        ];

        $resultData = [
            'emails'     => [$emailPositionsData],
            'totals'     => [],
            'events_count' => 0,
            'emails_count' => 0,
            'campaign_stats' => []
        ];

        $result = [
            'error'     => false,
            'message'   => __('Success.'),
            'emails'     => [$emailPositionsData],
            'totals'     => [],
            'events_count' => 0,
            'emails_count' => 0,
            'campaign_stats' => []
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();

        $this->emailManagementMock->expects($this->once())
            ->method('changePosition')
            ->with($emailId, $emailPosition)
            ->willReturn($emailMock);

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
     * Test execute method if an exception occurs
     */
    public function testExecuteException()
    {
        $eventId = 1;
        $emailId = 10;
        $emailPosition = 100;

        $emailOnePositionsData = [
            EmailInterface::ID => $emailId,
            EmailInterface::POSITION => $emailPosition,
        ];

        $data = [
            'event_id' => $eventId,
            'positions' => [$emailOnePositionsData],
        ];

        $exceptionMessage = __('Error!');
        $result = [
            'error'     => true,
            'message'   => $exceptionMessage,
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $this->emailManagementMock->expects($this->once())
            ->method('changePosition')
            ->with($emailId, $emailPosition)
            ->willThrowException(new \Exception($exceptionMessage));

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
     * Test execute method if not ajax request
     */
    public function testExecuteNoAjax()
    {
        $result = [
            'error'     => true,
            'message'   => __('Unknown error occured!')
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

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
     * Test execute method if no valid data
     * @dataProvider executeNoValidDataProvider
     */
    public function testExecuteNoValidData($data)
    {
        $result = [
            'error'     => true,
            'message'   => __('No data received!')
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

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
     * @return array
     */
    public function executeNoValidDataProvider()
    {
        return [
            [null],
            [[]],
            [['event_id' => null]],
            [['positions' => '']],
            [['event_id' => 1, 'positions' => '']],
            [['positions' => []]],
            [['event_id' => null, 'positions' => []]],
        ];
    }
}
