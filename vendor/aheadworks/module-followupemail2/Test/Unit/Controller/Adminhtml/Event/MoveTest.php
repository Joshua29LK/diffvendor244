<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Move;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Helper\Data as HelperData;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Move
 */
class MoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Move
     */
    private $controller;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var HelperData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperDataMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'helper'    => $this->helperDataMock,
            ]
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            Move::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'eventManagement' => $this->eventManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $campaignId = 1;
        $eventId = 2;
        $data = [
            'event_id' => $eventId,
            'campaign_id' => $campaignId
        ];
        $result =  [
            'error'     => false,
            'message'   => __('Success.')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagementMock->expects($this->once())
            ->method('changeCampaign')
            ->with($eventId, $campaignId)
            ->willReturn($eventMock);

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
     * Test execute method if redirect param is specified
     */
    public function testExecuteRedirect()
    {
        $campaignId = 1;
        $eventId = 2;
        $data = [
            'event_id' => $eventId,
            'campaign_id' => $campaignId,
            'redirect' => true
        ];
        $url = 'http://example.com/index.php/aw_followupemail2/event/index/campaign_id/' . $campaignId;
        $result =  [
            'error'         => false,
            'message'       => __('Success.'),
            'redirect_url'  => $url
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(false);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventManagementMock->expects($this->once())
            ->method('changeCampaign')
            ->with($eventId, $campaignId)
            ->willReturn($eventMock);

        $this->helperDataMock->expects($this->once())
            ->method('getUrl')
            ->with(
                'aw_followupemail2/event/index/',
                [
                    'campaign_id' => $campaignId,
                    '_secure' => false
                ]
            )
            ->willReturn($url);

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
     * Test execute method if an exception accurs
     */
    public function testExecuteException()
    {
        $campaignId = 1;
        $eventId = 2;
        $data = [
            'event_id' => $eventId,
            'campaign_id' => $campaignId
        ];
        $errorMessage = __('Error!');
        $result =  [
            'error'     => true,
            'message'   => $errorMessage
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $this->eventManagementMock->expects($this->once())
            ->method('changeCampaign')
            ->with($eventId, $campaignId)
            ->willThrowException(new LocalizedException($errorMessage));

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
     * Test execute method if no valid data specified
     * @param array|null $data
     * @dataProvider executeNoValidDataDataProvider
     */
    public function testExecuteNoValidData($data)
    {
        $result =  [
            'error'     => true,
            'message'   => __('No data specified!')
        ];

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

        $this->eventManagementMock->expects($this->never())
            ->method('changeCampaign');

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeNoValidDataDataProvider()
    {
        return [
            ['data' => null],
            ['data' => []],
            ['data' => ['event_id' => 1]],
            ['data' => ['campaign_id' => 1]],
            ['data' => ['event_id' => null, 'campaign_id' => null]],
        ];
    }
}
