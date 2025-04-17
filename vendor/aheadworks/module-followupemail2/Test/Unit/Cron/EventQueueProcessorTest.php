<?php
namespace Aheadworks\Followupemail2\Test\Unit\Cron;

use Aheadworks\Followupemail2\Cron\EventQueueProcessor;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Test for \Aheadworks\Followupemail2\Cron\EventQueueProcessor
 */
class EventQueueProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Event queue items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * @var EventQueueProcessor
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
     * @var EventQueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueManagementMock;

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
            ->setMethods(['isEnabled', 'getProcessEventQueueLastExecTime', 'setProcessEventQueueLastExecTime'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventQueueManagementMock = $this->getMockBuilder(EventQueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            EventQueueProcessor::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configMock,
                'eventQueueManagement' => $this->eventQueueManagementMock,
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
            ->method('getProcessEventQueueLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->eventQueueManagementMock->expects($this->once())
            ->method('processUnprocessedItems')
            ->with(self::ITEMS_PER_RUN)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('setProcessEventQueueLastExecTime')
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
            ->method('getProcessEventQueueLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->eventQueueManagementMock->expects($this->never())
            ->method('processUnprocessedItems')
            ->with(self::ITEMS_PER_RUN)
            ->willReturn(true);

        $this->configMock->expects($this->never())
            ->method('setProcessEventQueueLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

         $this->assertSame($this->model, $this->model->execute());
    }
}
