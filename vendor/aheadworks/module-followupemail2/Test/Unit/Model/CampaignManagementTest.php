<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Model\CampaignManagement;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;

/**
 * Test for \Aheadworks\Followupemail2\Model\CampaignManagement
 */
class CampaignManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CampaignManagement
     */
    private $model;

    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var EventTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypePoolMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->campaignRepositoryMock = $this->getMockBuilder(CampaignRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();
        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventTypePoolMock = $this->getMockBuilder(EventTypePool::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            CampaignManagement::class,
            [
                'campaignRepository' => $this->campaignRepositoryMock,
                'eventRepository' => $this->eventRepositoryMock,
                'eventManagement' => $this->eventManagementMock,
                'emailRepository' => $this->emailRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'eventTypePool' => $this->eventTypePoolMock
            ]
        );
    }

    /**
     * Test getActiveCampaigns method
     */
    public function testGetActiveCampaigns()
    {
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [CampaignInterface::STATUS, CampaignInterface::STATUS_ENABLED],
                [CampaignInterface::START_DATE, $this->greaterThanOrEqual(0)],
                [CampaignInterface::END_DATE, $this->greaterThanOrEqual(0)]
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignSearchResultsMock = $this->getMockBuilder(CampaignSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $campaignSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$campaignMock]);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($campaignSearchResultsMock);

        $this->assertEquals([$campaignMock], $this->model->getActiveCampaigns());
    }

    /**
     * Test duplicateCampaignEvents method
     */
    public function testDuplicateCampaignEvents()
    {
        $srcCampaignId = 1;
        $destCampaignId = 2;
        $srcEventId = 10;
        $descEventId = 11;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EventInterface::CAMPAIGN_ID, $srcCampaignId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventSrcMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventSrcMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($srcEventId);
        $eventDestMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventDestMock->expects($this->once())
            ->method('getId')
            ->willReturn($descEventId);
        $eventDestMock->expects($this->once())
            ->method('setId')
            ->with(null)
            ->willReturnSelf();
        $eventDestMock->expects($this->once())
            ->method('setCampaignId')
            ->with($destCampaignId)
            ->willReturnSelf();
        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventSrcMock]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($srcEventId)
            ->willReturn($eventDestMock);
        $this->eventRepositoryMock->expects($this->once())
            ->method('save')
            ->with($eventDestMock)
            ->willReturn($eventDestMock);

        $this->eventManagementMock->expects($this->once())
            ->method('duplicateEventEmails')
            ->with()
            ->willReturn(true);

        $this->assertTrue($this->model->duplicateCampaignEvents($srcCampaignId, $destCampaignId));
    }

    /**
     * Test getNewEventName method
     */
    public function testGetNewEventName()
    {
        $campaignId = 1;
        $eventName = 'Abandoned Cart';
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventTypeTitle = 'Abandoned Cart';

        $eventTypeMock = $this->getMockBuilder(TypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($eventTypeTitle);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->with($eventType)
            ->willReturn($eventTypeMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EventInterface::CAMPAIGN_ID, $campaignId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($eventName);
        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$eventMock]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);

        $this->assertEquals($eventName . ' #1', $this->model->getNewEventName($campaignId, $eventType));
    }

    /**
     * Test getEventsCount method
     */
    public function testGetEventsCount()
    {
        $campaignId = 1;
        $eventsCount = 2;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EventInterface::CAMPAIGN_ID, $campaignId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn($eventsCount);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);

        $this->assertEquals($eventsCount, $this->model->getEventsCount($campaignId));
    }

    /**
     * Test getEmailsCount method
     */
    public function testGetEmailsCount()
    {
        $campaignId = 1;
        $eventId = 10;
        $eventsCount = 1;
        $emailsCount = 2;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EventInterface::CAMPAIGN_ID, $campaignId, 'eq'],
                [EmailInterface::EVENT_ID, [$eventId], 'in']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);
        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn($eventsCount);
        $eventSearchResultsMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$eventMock]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);

        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn($emailsCount);
        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertEquals($emailsCount, $this->model->getEmailsCount($campaignId));
    }
}
