<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Controller\Adminhtml\Campaign\ResetStatistics;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign\ResetStatistics
 */
class ResetStatisticsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResetStatistics
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
     * @var StatisticsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsManagementMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var FormKey|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue', 'isAjax'])
            ->disableOriginalConstructor()
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
        $this->statisticsManagementMock = $this->getMockBuilder(StatisticsManagementInterface::class)
            ->getMockForAbstractClass();
        $this->formKeyMock = $this->getMockBuilder(FormKey::class)
            ->setMethods(['getFormKey'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            ResetStatistics::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'statisticsManagement' => $this->statisticsManagementMock,
                'formKey' => $this->formKeyMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $postData = [
            'id' => '10',
            'form_key' => 'XXXXXXXXX'
        ];
        $result =  [
            'error'     => false,
            'message'   => __('Success.')
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($postData['form_key']);

        $this->statisticsManagementMock->expects($this->once())
            ->method('resetByCampaignId')
            ->with($postData['id'])
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
        $result =  [
            'error'     => true,
            'message'   => __('Unknown error occured!')
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);

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
     * Test execute method when an exception occurs
     */
    public function testExecuteWithExcepton()
    {
        $postData = [
            'id' => '10',
            'form_key' => 'XXXXXXXXX'
        ];
        $result =  [
            'error'     => true,
            'message'   => __('Unknown error occured!')
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($postData['form_key']);

        $this->statisticsManagementMock->expects($this->once())
            ->method('resetByCampaignId')
            ->with($postData['id'])
            ->willThrowException(new \Exception($result['message']));

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
