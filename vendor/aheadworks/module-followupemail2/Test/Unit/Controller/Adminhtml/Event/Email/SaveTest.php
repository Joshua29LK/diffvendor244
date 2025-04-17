<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\PostDataProcessor;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ResponseDataProcessor;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\Save;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Save
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
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

    /**
     * @var EmailInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailFactoryMock;

    /**
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataProcessorMock;
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
            ->setMethods(['getPostValue'])
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

        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->emailFactoryMock = $this->getMockBuilder(EmailInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->postDataProcessorMock = $this->getMockBuilder(PostDataProcessor::class)
            ->setMethods(['prepareEntityData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseDataProcessorMock = $this->getMockBuilder(ResponseDataProcessor::class)
            ->setMethods(['getPreparedData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'emailRepository' => $this->emailRepositoryMock,
                'emailManagement' => $this->emailManagementMock,
                'emailFactory' => $this->emailFactoryMock,
                'queueManagement' => $this->queueManagementMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'postDataProcessor' => $this->postDataProcessorMock,
                'responseDataProcessor' => $this->responseDataProcessorMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $eventId = 2;
        $emailId = 10;
        $emailPosition = 100;
        $eventsCount = 3;
        $emailsCount = 4;
        $postData = [
            'id' => $emailId,
            'event_id' => $eventId,
            'name' => 'Test email name',
            'position' => $emailPosition,
            'content' => [],
        ];

        $resultData = [
            'emails'     => [],
            'totals'     => [],
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => []
        ];

        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'create' => false,
            'continue_edit' => false,
            'emails'     => [],
            'totals'     => [],
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => []
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($postData)
            ->willReturn($postData);

        $this->responseDataProcessorMock->expects($this->once())
            ->method('getPreparedData')
            ->with($eventId)
            ->willReturn($resultData);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $emailMock->expects($this->once())
            ->method('getPosition')
            ->willReturn($emailPosition);
        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($emailMock, $this->anything(), EmailInterface::class)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

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

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method if send test email selected
     */
    public function testExecuteSendTestEmail()
    {
        $eventId = 2;
        $emailId = 10;
        $emailPosition = 100;
        $eventsCount = 3;
        $emailsCount = 4;

        $postData = [
            'id' => $emailId,
            'event_id' => $eventId,
            'name' => 'Test email name',
            'position' => $emailPosition,
            'content' => [],
            'sendtest' => 1,
            'content_id' => EmailInterface::CONTENT_VERSION_A
        ];

        $resultData = [
            'emails'     => [],
            'totals'     => [],
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => []
        ];

        $result =  [
            'error'     => false,
            'message'   => __('Email was successfully sent.'),
            'create' => false,
            'continue_edit' => false,
            'emails'     => [],
            'totals'     => [],
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => [],
            'send_test' => true
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($postData)
            ->willReturn($postData);

        $this->responseDataProcessorMock->expects($this->once())
            ->method('getPreparedData')
            ->with($eventId)
            ->willReturn($resultData);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $emailMock->expects($this->once())
            ->method('getPosition')
            ->willReturn($emailPosition);
        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($emailMock, $this->anything(), EmailInterface::class)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->queueManagementMock->expects($this->once())
            ->method('sendTest')
            ->with($emailMock, EmailInterface::CONTENT_VERSION_A)
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

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no data specified
     */
    public function testExecuteNoDataSpecified()
    {
        $result =  [
            'error'     => true,
            'message'   => __('No data specified!')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
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

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no email with specified id
     */
    public function testExecuteWithExcepton()
    {
        $emailId = 1;
        $postData = [
            'id' => $emailId
        ];

        $result =  [
            'error'     => true,
            'message'   => __('No such entity.')
        ];
        $exception = new NoSuchEntityException();

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
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

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }
}
