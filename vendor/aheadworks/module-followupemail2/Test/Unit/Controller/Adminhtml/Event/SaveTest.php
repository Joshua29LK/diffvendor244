<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\PostDataProcessor;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Save;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Ui\DataProvider\Event\ManageFormProcessor;
use Aheadworks\Followupemail2\Model\Event\TypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Save
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
     * @var CampaignManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignManagementMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * @var EventInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventFactoryMock;

    /**
     * @var ManageFormProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manageFormProcessorMock;

    /**
     * @var EventTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypePoolMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataProcessorMock;

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
        $this->campaignManagementMock = $this->getMockBuilder(CampaignManagementInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();
        $this->eventFactoryMock = $this->getMockBuilder(EventInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->manageFormProcessorMock = $this->getMockBuilder(ManageFormProcessor::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventTypePoolMock = $this->getMockBuilder(EventTypePool::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->setMethods(['buildOutputDataArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->postDataProcessorMock = $this->getMockBuilder(PostDataProcessor::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'campaignManagement' => $this->campaignManagementMock,
                'eventRepository' => $this->eventRepositoryMock,
                'eventManagement' => $this->eventManagementMock,
                'eventFactory' => $this->eventFactoryMock,
                'manageFormProcessor' => $this->manageFormProcessorMock,
                'eventTypePool' => $this->eventTypePoolMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'postDataProcessor' => $this->postDataProcessorMock,
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
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventTypeLabel = __('Abandoned Cart');
        $eventsCount = 1;
        $emailsCount = 1;
        $conditionsSerialized = '';
        $postData = [
            'id' => $eventId,
            'campaign_id' => $campaignId,
            'event_type' => $eventType,
            'name' => "For customers with lifetime sales OVER $500",
            'newsletter_only' => '',
            'failed_emails_mode' => "1",
            'store_ids' => ["0"],
            'product_type_ids' => ["all"],
            'cart_conditions' => "[]",
            'lifetime_conditions' => "gt",
            'customer_groups' => ["all"],
            'order_statuses' => ["all"],
            'status' => "0",
            'lifetime_value' => "500",
            'lifetime_from' => "",
            'lifetime_to' => "",
            'duplicate_id' => false,
            'bcc_emails' => "",
            'rule' => [
                'conditions' => [
                    '1' => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => ''],
                    '1--1' => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'attribute' => 'base_subtotal',
                        'operator' => '>',
                        'value' => '50'
                    ]
                ]
            ]
        ];
        $eventData = [
            'event_type_label' => $eventTypeLabel,
            'emails' => [],
            'totals' => []
        ];
        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'event' => $eventData,
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'create' => false
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($postData)
            ->willReturn($postData);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);
        $eventMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);
        $eventMock->expects($this->atLeastOnce())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);
        $this->eventRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventMock)
            ->willReturn($eventMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($eventMock, $this->anything(), EventInterface::class)
            ->willReturnSelf();
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($eventMock, EventInterface::class)
            ->willReturn($eventData);

        $typeMock = $this->getMockBuilder(TypeInterface::class)
            ->getMockForAbstractClass();
        $typeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($eventTypeLabel);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($typeMock);

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventEmailsData')
            ->with($eventId)
            ->willReturn([]);
        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventTotals')
            ->with($eventId)
            ->willReturn([]);

        $this->campaignManagementMock->expects($this->once())
            ->method('getEventsCount')
            ->with($campaignId)
            ->willReturn($eventsCount);
        $this->campaignManagementMock->expects($this->once())
            ->method('getEmailsCount')
            ->with($campaignId)
            ->willReturn($emailsCount);

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
     * Test execute method if duplicate event triggered
     */
    public function testExecuteSendTestEmail()
    {
        $campaignId = 1;
        $srcEventId = 2;
        $eventId = 3;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventTypeLabel = __('Abandoned Cart');
        $eventsCount = 1;
        $emailsCount = 1;
        $conditionsSerialized = '';
        $postData = [
            'id' => $eventId,
            'campaign_id' => $campaignId,
            'event_type' => $eventType,
            'name' => "For customers with lifetime sales OVER $500",
            'newsletter_only' => '',
            'failed_emails_mode' => "1",
            'store_ids' => ["0"],
            'product_type_ids' => ["all"],
            'cart_conditions' => "[]",
            'lifetime_conditions' => "gt",
            'customer_groups' => ["all"],
            'order_statuses' => ["all"],
            'status' => "0",
            'lifetime_value' => "500",
            'lifetime_from' => "",
            'lifetime_to' => "",
            'duplicate_id' => $srcEventId,
            'bcc_emails' => "",
            'rule' => []
        ];
        $eventData = [
            'event_type_label' => $eventTypeLabel,
            'emails' => [],
            'totals' => []
        ];
        $result =  [
            'error'     => false,
            'message'   => __('Success.'),
            'event' => $eventData,
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'create' => false
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($postData)
            ->willReturn($postData);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);
        $eventMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);
        $eventMock->expects($this->atLeastOnce())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);
        $this->eventRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventMock)
            ->willReturn($eventMock);

        $this->eventManagementMock->expects($this->once())
            ->method('duplicateEventEmails')
            ->with($srcEventId, $eventId)
            ->willReturn(true);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($eventMock, $this->anything(), EventInterface::class)
            ->willReturnSelf();
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($eventMock, EventInterface::class)
            ->willReturn($eventData);

        $typeMock = $this->getMockBuilder(TypeInterface::class)
            ->getMockForAbstractClass();
        $typeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($eventTypeLabel);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->willReturn($typeMock);

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventEmailsData')
            ->with($eventId)
            ->willReturn([]);
        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventTotals')
            ->with($eventId)
            ->willReturn([]);

        $this->campaignManagementMock->expects($this->once())
            ->method('getEventsCount')
            ->with($campaignId)
            ->willReturn($eventsCount);
        $this->campaignManagementMock->expects($this->once())
            ->method('getEmailsCount')
            ->with($campaignId)
            ->willReturn($emailsCount);

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
     * Test execute method when no event with specified id
     */
    public function testExecuteWithExcepton()
    {
        $eventId = 1;
        $postData = [
            'id' => $eventId
        ];

        $result =  [
            'error'     => true,
            'message'   => __('No such entity.')
        ];
        $exception = new NoSuchEntityException();

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
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
