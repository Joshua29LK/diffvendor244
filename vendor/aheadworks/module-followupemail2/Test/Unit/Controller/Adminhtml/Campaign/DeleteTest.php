<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Controller\Adminhtml\Campaign\Delete;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign\Delete
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
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

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
        $this->campaignRepositoryMock = $this->getMockForAbstractClass(CampaignRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'campaignRepository' => $this->campaignRepositoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $requestParam = 'id';
        $requestParamValue = 10;
        $result =  [
            'error'     => false,
            'message'   => __('Success.')
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn($requestParamValue);

        $this->campaignRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($requestParamValue)
            ->willReturn(true);

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
     * Test execute method when no campaign id specified
     */
    public function testExecuteNoIdSpecified()
    {
        $requestParam = 'id';
        $result =  [
            'error'     => true,
            'message'   => __('Campaign Id is not specified!')
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
     * Test execute method when no campaign with specified id
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

        $this->campaignRepositoryMock->expects($this->once())
            ->method('deleteById')
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
