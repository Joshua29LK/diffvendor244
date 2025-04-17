<?php
namespace Aheadworks\Followupemail2\Test\Unit\Cron;

use Aheadworks\Followupemail2\Cron\EventHistoryProcessor;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Test for \Aheadworks\Followupemail2\Cron\EventHistoryProcessor
 */
class EventHistoryProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Event queue items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * @var EventHistoryProcessor
     */
    private $model;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EventHistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->setMethods(['timestamp'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isEnabled', 'getProcessEventHistoryLastExecTime', 'setProcessEventHistoryLastExecTime'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryManagementMock = $this->getMockBuilder(EventHistoryManagementInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            EventHistoryProcessor::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configMock,
                'eventHistoryManagement' => $this->eventHistoryManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 360;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getProcessEventHistoryLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->eventHistoryManagementMock->expects($this->once())
            ->method('processUnprocessedItems')
            ->with(self::ITEMS_PER_RUN)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('setProcessEventHistoryLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

        $this->assertSame($this->model, $this->model->execute());
    }

    /**
     * Test execute method if it started too often
     */
    public function testExecuteTooOften()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 200;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getProcessEventHistoryLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('processUnprocessedItems')
            ->with(self::ITEMS_PER_RUN)
            ->willReturn(true);

        $this->configMock->expects($this->never())
            ->method('setProcessEventHistoryLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

         $this->assertSame($this->model, $this->model->execute());
    }
}
