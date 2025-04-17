<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Delete;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Delete
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
     * @var CampaignManagementInterface
     */
    private $campaignManagementMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

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
        $this->campaignManagementMock = $this->getMockForAbstractClass(CampaignManagementInterface::class);
        $this->eventRepositoryMock = $this->getMockForAbstractClass(EventRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'campaignManagement' => $this->campaignManagementMock,
                'eventRepository' => $this->eventRepositoryMock
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
        $campaignId = 3;
        $eventsCount = 1;
        $emailsCount = 2;
        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'events_count' => 1,
            'emails_count' => 2,
            'campaign_stats' => null
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestParam)
            ->willReturn($requestParamValue);

        $this->campaignManagementMock->expects($this->once())
            ->method('getEventsCount')
            ->with($campaignId)
            ->willReturn($eventsCount);
        $this->campaignManagementMock->expects($this->once())
            ->method('getEmailsCount')
            ->with($campaignId)
            ->willReturn($emailsCount);

        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestParamValue)
            ->willReturn($eventMock);
        $this->eventRepositoryMock->expects($this->once())
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
     * Test execute method when no event id specified
     */
    public function testExecuteNoIdSpecified()
    {
        $requestParam = 'id';
        $result =  [
            'error'     => true,
            'message'   => __('Event Id is not specified!')
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
     * Test execute method when no event with specified id
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

        $this->eventRepositoryMock->expects($this->once())
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
