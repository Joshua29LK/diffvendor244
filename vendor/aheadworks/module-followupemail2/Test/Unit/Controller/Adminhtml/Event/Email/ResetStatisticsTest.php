<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ResetStatistics;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Backend\Helper\Data as HelperData;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\ResetStatistics
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
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var StatisticsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsManagementMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var HelperData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperDataMock;

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
            ->setMethods(['getPostValue', 'isAjax', 'isSecure'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request'   => $this->requestMock,
                'helper'    => $this->helperDataMock
            ]
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
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
                'eventRepository' => $this->eventRepositoryMock,
                'emailRepository' => $this->emailRepositoryMock,
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
            'email_id' => '10',
            'content_id' => '100',
            'form_key' => 'XXXXXXXXX'
        ];
        $eventId = 5;
        $campaignId = 2;
        $url = 'http://example.com/index.php/aw_followupemail2/event/index/campaign_id/' . $campaignId;
        $result =  [
            'error'         => false,
            'message'       => __('Success.'),
            'redirect_url'  => $url
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(false);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($postData['form_key']);

        $this->statisticsManagementMock->expects($this->once())
            ->method('resetByEmailContentId')
            ->with($postData['content_id'])
            ->willReturn(true);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($postData['email_id'])
            ->willReturn($emailMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
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
     * Test execute method when no content id specified
     */
    public function testExecuteNoContentId()
    {
        $postData = [
            'email_id' => '10',
            'form_key' => 'XXXXXXXXX'
        ];
        $eventId = 5;
        $campaignId = 2;
        $url = 'http://example.com/index.php/aw_followupemail2/event/index/campaign_id/' . $campaignId;
        $result =  [
            'error'         => false,
            'message'       => __('Success.'),
            'redirect_url'  => $url
        ];

        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(false);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($postData['form_key']);

        $this->statisticsManagementMock->expects($this->once())
            ->method('resetByEmailId')
            ->with($postData['email_id'])
            ->willReturn(true);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getEventId')
            ->willReturn($eventId);
        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($postData['email_id'])
            ->willReturn($emailMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
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
     * Test execute method when no email id specified
     */
    public function testExecuteNoEmailId()
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
            'email_id' => '10',
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
            ->method('resetByEmailId')
            ->with($postData['email_id'])
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
