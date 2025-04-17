<?php
namespace Aheadworks\Followupemail2\Test\Unit\Cron;

use Aheadworks\Followupemail2\Cron\MailLogCleaner;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Test for \Aheadworks\Followupemail2\Cron\MailLogCleaner
 */
class MailLogCleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MailLogCleaner
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
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

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
            ->setMethods(['isEnabled', 'getClearLogLastExecTime', 'setClearLogLastExecTime', 'getKeepEmailsFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            MailLogCleaner::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configMock,
                'queueManagement' => $this->queueManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 87000;
        $keepEmailsFor = 60;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getClearLogLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);
        $this->configMock->expects($this->once())
            ->method('getKeepEmailsFor')
            ->willReturn($keepEmailsFor);

        $this->queueManagementMock->expects($this->once())
            ->method('clearQueue')
            ->with($keepEmailsFor)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('setClearLogLastExecTime')
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

        $keepEmailsFor = 60;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getClearLogLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);
        $this->configMock->expects($this->never())
            ->method('getKeepEmailsFor')
            ->willReturn($keepEmailsFor);

        $this->queueManagementMock->expects($this->never())
            ->method('clearQueue')
            ->with($keepEmailsFor)
            ->willReturn(true);

        $this->configMock->expects($this->never())
            ->method('setClearLogLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

        $this->assertSame($this->model, $this->model->execute());
    }
}
