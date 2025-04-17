<?php
namespace Aheadworks\Followupemail2\Test\Unit\Block\Adminhtml\Event;

use Aheadworks\Followupemail2\Block\Adminhtml\Event\Campaign;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Test for \Aheadworks\Followupemail2\Block\Adminhtml\Event\Campaign
 */
class CampaignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Campaign
     */
    private $block;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

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
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var array
     */
    private $campaignData = [
        'id' => 1,
        'name' => 'Test campaign',
        'description' => 'Test description',
        'start_date' => '2010-01-01 00:00:00',
        'end_date' => '2029-01-01 00:00:00',
        'status' => 1,
        'events_count' => 1,
        'emails_count' => 2
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMockForAbstractClass();

        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'localeDate' => $this->localeDateMock
            ]
        );

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->campaignRepositoryMock = $this->getMockBuilder(CampaignRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock = $this->getMockBuilder(StatisticsManagementInterface::class)
            ->getMockForAbstractClass();

        $this->block = $objectManager->getObject(
            Campaign::class,
            [
                'context' => $contextMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'campaignRepository'    => $this->campaignRepositoryMock,
                'eventRepository'       => $this->eventRepositoryMock,
                'emailRepository'       => $this->emailRepositoryMock,
                'statisticsManagement' => $this->statisticsManagementMock,
                'data' => []
            ]
        );
    }

    /**
     * Test getName method
     */
    public function testGetName()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getName')
            ->willReturn($this->campaignData['name']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->assertSame($this->campaignData['name'], $this->block->getName());
    }

    /**
     * Test getName method if no campaign specified
     */
    public function testGetNameNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertSame('', $this->block->getName());
    }

    /**
     * Test getName method if campaign can not be found
     */
    public function testGetNameNoCampaignException()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);

        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willThrowException(
                new NoSuchEntityException(__('No such entity with id = ?', $this->campaignData['id']))
            );

        $this->assertSame('', $this->block->getName());
    }

    /**
     * Test getDescription method
     */
    public function testGetDescription()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getDescription')
            ->willReturn($this->campaignData['description']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->assertSame($this->campaignData['description'], $this->block->getDescription());
    }

    /**
     * Test getDescription method if no campaign specified
     */
    public function testGetDescriptionNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertSame('', $this->block->getDescription());
    }

    /**
     * Test hasDateSelected method if start date is set
     */
    public function testHasDateSelectedStartDate()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getStartDate')
            ->willReturn($this->campaignData['start_date']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->assertTrue($this->block->hasDateSelected());
    }

    /**
     * Test hasDateSelected method if end date is set
     */
    public function testHasDateSelectedEndDate()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getEndDate')
            ->willReturn($this->campaignData['end_date']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->assertTrue($this->block->hasDateSelected());
    }

    /**
     * Test hasDateSelected method if no date is set
     */
    public function testHasDateSelectedNoDate()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getStartDate')
            ->willReturn(null);
        $campaignMock->expects($this->once())
            ->method('getEndDate')
            ->willReturn(null);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->assertFalse($this->block->hasDateSelected());
    }

    /**
     * Test hasDateSelected method if no campaign specified
     */
    public function testHasDateSelectedNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertFalse($this->block->hasDateSelected());
    }

    /**
     * Test getStartDate method
     */
    public function testGetStartDate()
    {
        $humanDate = 'Jan 01, 2010 0:00:00 PM';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getStartDate')
            ->willReturn($this->campaignData['start_date']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->willReturn($humanDate);

        $this->assertSame($humanDate, $this->block->getStartDate());
    }

    /**
     * Test getStartDate method if no campaign specified
     */
    public function testGetStartDateNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertNull($this->block->getStartDate());
    }

    /**
     * Test getEndDate method
     */
    public function testGetEndDate()
    {
        $humanDate = 'Jan 01, 2029 0:00:00 PM';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getEndDate')
            ->willReturn($this->campaignData['end_date']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->willReturn($humanDate);

        $this->assertSame($humanDate, $this->block->getEndDate());
    }

    /**
     * Test getEndDate method if no campaign specified
     */
    public function testGetEndDateNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertNull($this->block->getEndDate());
    }

    /**
     * Test getEventsCount method
     */
    public function testGetEventsCount()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->campaignData['id']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($this->campaignData['events_count']);
        $eventSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventMock]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);

        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($this->campaignData['emails_count']);
        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertSame($this->campaignData['events_count'], $this->block->getEventsCount());
    }

    /**
     * Test getEventsCount method if no campaign specified
     */
    public function testGetEventsCountNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertEquals(0, $this->block->getEventsCount());
    }

    /**
     * Test getEmailsCount method
     */
    public function testGetEmailsCount()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->campaignData['id']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $eventSearchResultsMock = $this->getMockBuilder(EventSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $eventSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($this->campaignData['events_count']);
        $eventSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$eventMock]);
        $this->eventRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($eventSearchResultsMock);

        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($this->campaignData['emails_count']);
        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertSame($this->campaignData['emails_count'], $this->block->getEmailsCount());
    }

    /**
     * Test getEmailsCount method if no campaign specified
     */
    public function testGetEmailsCountNoCampaign()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertEquals(0, $this->block->getEmailsCount());
    }

    /**
     * Test getEmailStatistics method
     */
    public function testGetEmailStatistics()
    {
        $result = [
            'sent'          => 3,
            'opened'        => 2,
            'clicks'        => 1,
            'open_rate'     => 66.66,
            'click_rate'    => 50.00
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn($this->campaignData['id']);
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->campaignData['id']);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($campaignMock);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $statisticsMock->expects($this->once())
            ->method('getSent')
            ->willReturn($result['sent']);
        $statisticsMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($result['opened']);
        $statisticsMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($result['clicks']);
        $statisticsMock->expects($this->once())
            ->method('getOpenRate')
            ->willReturn($result['open_rate']);
        $statisticsMock->expects($this->once())
            ->method('getClickRate')
            ->willReturn($result['click_rate']);

        $this->statisticsManagementMock->expects($this->once())
            ->method('getByCampaignId')
            ->with($this->campaignData['id'])
            ->willReturn($statisticsMock);

        $this->assertSame($result, $this->block->getEmailStatistics());
    }

    /**
     * Test getEmailStatistics method if no campaign specified
     */
    public function testGetEmailStatisticsNoCampaign()
    {
        $result = [
            'sent'          => 0,
            'opened'        => 0,
            'clicks'        => 0,
            'open_rate'     => 0,
            'click_rate'    => 0
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('campaign_id')
            ->willReturn(null);

        $this->assertSame($result, $this->block->getEmailStatistics());
    }
}
