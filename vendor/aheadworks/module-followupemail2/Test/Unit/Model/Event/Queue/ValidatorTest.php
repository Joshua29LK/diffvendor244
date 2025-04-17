<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Model\Event\Queue\Validator as EventQueueValidator;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventQueueValidator
     */
    private $model;

    /**
     * @var CampaignManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignManagementMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->campaignManagementMock = $this->getMockBuilder(CampaignManagementInterface::class)
            ->getMockForAbstractClass();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->setMethods(['timestamp'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EventQueueValidator::class,
            [
                'campaignManagement' => $this->campaignManagementMock,
                'dateTime' => $this->dateTimeMock,
            ]
        );
    }

    /**
     * Test isEventValid method
     *
     * @param int $status
     * @param int $campaignId
     * @param array $activeCampaignMocks
     * @param bool $result
     * @dataProvider isEventValidDataProvider
     */
    public function testIsEventValid($status, $campaignId, $activeCampaignMocks, $result)
    {
        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        if ($status == EventInterface::STATUS_ENABLED) {
            $eventMock->expects($this->once())
                ->method('getCampaignId')
                ->willReturn($campaignId);

            $this->campaignManagementMock->expects($this->once())
                ->method('getActiveCampaigns')
                ->willReturn($activeCampaignMocks);
        }

        $this->assertEquals($result, $this->model->isEventValid($eventMock));
    }

    /**
     * @return array
     */
    public function isEventValidDataProvider()
    {
        $campaignOneId = 1;
        $campaignTwoId = 2;

        $campaignOneMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignOneMock->expects($this->any())
            ->method('getId')
            ->willReturn($campaignOneId);

        $campaignTwoMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignTwoMock->expects($this->any())
            ->method('getId')
            ->willReturn($campaignTwoId);

        return [
            [
                'status' => EventInterface::STATUS_DISABLED,
                'campaignId' => $campaignTwoId,
                'activeCampaigns' => [],
                'result' => false
            ],
            [
                'status' => EventInterface::STATUS_ENABLED,
                'campaignId' => $campaignTwoId,
                'activeCampaigns' => [],
                'result' => false
            ],
            [
                'status' => EventInterface::STATUS_ENABLED,
                'campaignId' => $campaignTwoId,
                'activeCampaigns' => [$campaignOneMock],
                'result' => false
            ],
            [
                'status' => EventInterface::STATUS_ENABLED,
                'campaignId' => $campaignOneId,
                'activeCampaigns' => [$campaignOneMock, $campaignTwoMock],
                'result' => true
            ],
        ];
    }

    /**
     *  Test isEmailValidToSend method
     *
     * @param string $lastSentDate
     * @param int $delta
     * @param bool $result
     * @dataProvider isEmailValidToSendDataProvider
     */
    public function testIsEmailValidToSend($lastSentDate, $delta, $result)
    {
        $days = 1;
        $hours = 2;
        $minutes = 3;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getWhen')
            ->willReturn(EmailInterface::WHEN_AFTER);
        $emailMock->expects($this->once())
            ->method('getEmailSendDays')
            ->willReturn($days);
        $emailMock->expects($this->once())
            ->method('getEmailSendHours')
            ->willReturn($hours);
        $emailMock->expects($this->once())
            ->method('getEmailSendMinutes')
            ->willReturn($minutes);

        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->withConsecutive([$lastSentDate], [null])
            ->willReturnOnConsecutiveCalls(0, $delta);

        $this->assertEquals($result, $this->model->isEmailValidToSend($emailMock, $lastSentDate));
    }

    /**
     * @return array
     */
    public function isEmailValidToSendDataProvider()
    {
        $timestampDelta = 60 * (3 + 60 * (2 + 1 * 24));
        return [
            ['2018-01-01 00:00:00', $timestampDelta - 1, false],
            ['2018-01-01 00:00:00', $timestampDelta, true],
        ];
    }

    /**
     *  Test isEmailValidToSend method if an email has prediction enabled
     */
    public function testIsEmailValidToSendPrediction()
    {
        $lastSentDate = '2018-01-01 00:00:00';
        $delta = 123;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getWhen')
            ->willReturn(EmailInterface::WHEN_BEFORE);
        $emailMock->expects($this->never())
            ->method('getEmailSendDays');
        $emailMock->expects($this->never())
            ->method('getEmailSendHours');
        $emailMock->expects($this->never())
            ->method('getEmailSendMinutes');

        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->withConsecutive([$lastSentDate], [null])
            ->willReturnOnConsecutiveCalls(0, $delta);

        $this->assertTrue($this->model->isEmailValidToSend($emailMock, $lastSentDate));
    }
}
