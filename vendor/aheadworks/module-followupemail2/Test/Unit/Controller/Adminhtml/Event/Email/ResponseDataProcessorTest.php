<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\ResponseDataProcessor;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Source\Email\Status as EmailStatusSource;
use Aheadworks\Followupemail2\Ui\DataProvider\Event\ManageFormProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\ResponseDataProcessor
 */
class ResponseDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseDataProcessor
     */
    private $model;

    /**
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

    /**
     * @var EmailStatusSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailStatusSourceMock;

    /**
     * @var ManageFormProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manageFormProcessorMock;

    /**
     * @var CampaignManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignManagementMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var StatisticsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsManagementMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->emailStatusSourceMock = $this->getMockBuilder(EmailStatusSource::class)
            ->setMethods(['getOptionByValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->manageFormProcessorMock = $this->getMockBuilder(ManageFormProcessor::class)
            ->setMethods(['getWhen', 'getEventTotals'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->campaignManagementMock = $this->getMockBuilder(CampaignManagementInterface::class)
            ->getMockForAbstractClass();

        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->statisticsManagementMock = $this->getMockBuilder(StatisticsManagementInterface::class)
            ->getMockForAbstractClass();

        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->setMethods(['buildOutputDataArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ResponseDataProcessor::class,
            [
                'emailManagement' => $this->emailManagementMock,
                'emailStatusSource' => $this->emailStatusSourceMock,
                'manageFormProcessor' => $this->manageFormProcessorMock,
                'campaignManagement' => $this->campaignManagementMock,
                'eventRepository' => $this->eventRepositoryMock,
                'statisticsManagement' => $this->statisticsManagementMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
            ]
        );
    }

    /**
     * Test getPreparedData method
     */
    public function testGetPreparedData()
    {
        $campaignId = 1;
        $eventId = 2;
        $emailId = 3;
        $emailName = 'Test email';
        $emailStatus = EmailInterface::STATUS_ENABLED;

        $emailData = [
            EmailInterface::ID => $emailId,
            EmailInterface::NAME => $emailName,
            EmailInterface::STATUS => $emailStatus,
        ];

        $when = '3 days after event triggered';
        $sent = 10;
        $opened = 8;
        $clicked = 3;
        $openRate = $opened / $sent * 100;
        $clickRate = $clicked / $sent * 100;
        $statusLabel = 'Enabled';
        $resultEmailData = array_merge(
            $emailData,
            [
                'when' => $when,
                'sent' => $sent,
                'opened' => $opened,
                'clicks' => $clicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'status' => $statusLabel,
                'is_email_disabled' => $emailStatus == EmailInterface::STATUS_DISABLED,
            ]
        );

        $totals = [
            'sent' => 5,
            'opened' => 0,
            'clicks' => 0,
            'open_rate' => 0.00,
            'click_rate' => 0.00
        ];

        $eventsCount = 4;
        $emailsCount = 5;
        $stats = [
            'sent' => 3,
            'opened' => 2,
            'clicks' => 1,
            'open_rate' => 66.66,
            'click_rate' => 50.00,
        ];

        $result = [
            'emails' => [$resultEmailData],
            'totals' => $totals,
            'events_count' => $eventsCount,
            'emails_count' => $emailsCount,
            'campaign_stats' => $stats
        ];

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getCampaignId')
            ->willReturn($campaignId);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($emailStatus);
        $this->emailManagementMock->expects($this->once())
            ->method('getEmailsByEventId')
            ->with($eventId)
            ->willReturn([$emailMock]);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock->expects($this->once())
            ->method('getByCampaignId')
            ->with($campaignId)
            ->willReturn($statisticsMock);

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventTotals')
            ->with($eventId)
            ->willReturn($totals);

        $this->dataObjectProcessorMock->expects($this->exactly(2))
            ->method('buildOutputDataArray')
            ->withConsecutive([$emailMock], [$statisticsMock])
            ->willReturnOnConsecutiveCalls($emailData, $stats);

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getWhen')
            ->willReturn($when);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $statisticsMock->expects($this->once())
            ->method('getSent')
            ->willReturn($sent);
        $statisticsMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($opened);
        $statisticsMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($clicked);
        $statisticsMock->expects($this->once())
            ->method('getOpenRate')
            ->willReturn($openRate);
        $statisticsMock->expects($this->once())
            ->method('getClickRate')
            ->willReturn($clickRate);

        $this->emailManagementMock->expects($this->once())
            ->method('getStatistics')
            ->with($emailMock)
            ->willReturn($statisticsMock);

        $this->emailStatusSourceMock->expects($this->once())
            ->method('getOptionByValue')
            ->with($emailStatus)
            ->willReturn($statusLabel);

        $this->campaignManagementMock->expects($this->once())
            ->method('getEventsCount')
            ->with($campaignId)
            ->willReturn($eventsCount);
        $this->campaignManagementMock->expects($this->once())
            ->method('getEmailsCount')
            ->with($campaignId)
            ->willReturn($emailsCount);

        $this->assertEquals($result, $this->model->getPreparedData($eventId));
    }

    /**
     * Test getPreparedData method if no event
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetPreparedDataNoEvent()
    {
        $eventId = 1;
        $exceptionMessage = __('No such entity with id = 1');

        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willThrowException(new NoSuchEntityException($exceptionMessage));
        $this->expectException(NoSuchEntityException::class);
        $this->model->getPreparedData($eventId);
    }

    /**
     * Test getData method
     */
    public function testGetData()
    {
        $emailId = 1;
        $emailName = 'Test email';
        $emailStatus = EmailInterface::STATUS_ENABLED;

        $emailData = [
            EmailInterface::ID => $emailId,
            EmailInterface::NAME => $emailName,
            EmailInterface::STATUS => $emailStatus,
        ];

        $when = '3 days after event triggered';
        $sent = 10;
        $opened = 8;
        $clicked = 3;
        $openRate = $opened / $sent * 100;
        $clickRate = $clicked / $sent * 100;
        $statusLabel = 'Enabled';
        $resultData = array_merge(
            $emailData,
            [
                'when' => $when,
                'sent' => $sent,
                'opened' => $opened,
                'clicks' => $clicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'status' => $statusLabel,
                'is_email_disabled' => $emailStatus == EmailInterface::STATUS_DISABLED,
            ]
        );

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($emailStatus);

        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($emailMock)
            ->willReturn($emailData);

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getWhen')
            ->willReturn($when);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $statisticsMock->expects($this->once())
            ->method('getSent')
            ->willReturn($sent);
        $statisticsMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($opened);
        $statisticsMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($clicked);
        $statisticsMock->expects($this->once())
            ->method('getOpenRate')
            ->willReturn($openRate);
        $statisticsMock->expects($this->once())
            ->method('getClickRate')
            ->willReturn($clickRate);

        $this->emailManagementMock->expects($this->once())
            ->method('getStatistics')
            ->with($emailMock)
            ->willReturn($statisticsMock);

        $this->emailStatusSourceMock->expects($this->once())
            ->method('getOptionByValue')
            ->with($emailStatus)
            ->willReturn($statusLabel);

        $this->assertEquals($resultData, $this->model->getData($emailMock));
    }

    /**
     * Test addStatisticsData method
     */
    public function testAddStatisticsData()
    {
        $campaignId = 1;
        $sourceData = [];

        $eventsCount = 4;
        $emailsCount = 5;
        $stats = [
            'sent' => 3,
            'opened' => 2,
            'clicks' => 1,
            'open_rate' => 66.66,
            'click_rate' => 50.00,
        ];
        $resultData = array_merge(
            $sourceData,
            [
                'events_count' => $eventsCount,
                'emails_count' => $emailsCount,
                'campaign_stats' => $stats
            ]
        );

        $this->campaignManagementMock->expects($this->once())
            ->method('getEventsCount')
            ->with($campaignId)
            ->willReturn($eventsCount);
        $this->campaignManagementMock->expects($this->once())
            ->method('getEmailsCount')
            ->with($campaignId)
            ->willReturn($emailsCount);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock->expects($this->once())
            ->method('getByCampaignId')
            ->with($campaignId)
            ->willReturn($statisticsMock);

        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($statisticsMock)
            ->willReturn($stats);

        $this->assertEquals($resultData, $this->model->addStatisticsData($campaignId, $sourceData));
    }

    /**
     * Test addStatisticsData method if no campaign id specified
     */
    public function testAddStatisticsDataNoCampaighId()
    {
        $campaignId = null;
        $sourceData = [];

        $resultData = [
            'events_count' => 0,
            'emails_count' => 0,
            'campaign_stats' => []
        ];

        $this->assertEquals($resultData, $this->model->addStatisticsData($campaignId, $sourceData));
    }

    /**
     * Test addEventTotals method
     */
    public function testAddEventTotals()
    {
        $eventId = 1;
        $sourceData = [];

        $totals = [
            'sent' => 5,
            'opened' => 0,
            'clicks' => 0,
            'open_rate' => 0.00,
            'click_rate' => 0.00
        ];

        $resultData = [
            'totals' => $totals
        ];

        $this->manageFormProcessorMock->expects($this->once())
            ->method('getEventTotals')
            ->with($eventId)
            ->willReturn($totals);

        $this->assertEquals($resultData, $this->model->addEventTotals($eventId, $sourceData));
    }
}
