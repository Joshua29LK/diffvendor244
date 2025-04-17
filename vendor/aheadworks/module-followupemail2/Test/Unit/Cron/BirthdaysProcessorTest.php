<?php
namespace Aheadworks\Followupemail2\Test\Unit\Cron;

use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Cron\BirthdaysProcessor;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Model\Event\HandlerInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Test for \Aheadworks\Followupemail2\Cron\BirthdaysProcessor
 */
class BirthdaysProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Event queue items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * @var BirthdaysProcessor
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
     * @var EventTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventTypePoolMock;

    /**
     * @var EventHistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryItemFactoryMock;

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
            ->setMethods(['isEnabled', 'getProcessBirthdaysLastExecTime', 'setProcessBirthdaysLastExecTime'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventTypePoolMock = $this->getMockBuilder(EventTypePool::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryItemFactoryMock = $this->getMockBuilder(EventHistoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            BirthdaysProcessor::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configMock,
                'eventTypePool' => $this->eventTypePoolMock,
                'eventHistoryItemFactory' => $this->eventHistoryItemFactoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 86000;
        $isModuleEnabled = true;
        $isTypeEnabled = true;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isModuleEnabled);
        $this->configMock->expects($this->once())
            ->method('getProcessBirthdaysLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $eventHistoryItemMock = $this->getMockBuilder(EventHistoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventHistoryItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryItemMock);

        $eventHandlerMock = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $eventHandlerMock->expects($this->once())
            ->method('process')
            ->with($eventHistoryItemMock)
            ->willReturn(null);

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isTypeEnabled);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn($eventHandlerMock);

        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->with(EventInterface::TYPE_CUSTOMER_BIRTHDAY)
            ->willReturn($eventTypeMock);

        $this->configMock->expects($this->once())
            ->method('setProcessBirthdaysLastExecTime')
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
            ->method('getProcessBirthdaysLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->configMock->expects($this->never())
            ->method('setProcessBirthdaysLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

         $this->assertSame($this->model, $this->model->execute());
    }

    /**
     * Test execute method if no event type found
     */
    public function testExecuteNoEventType()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 86000;
        $isModuleEnabled = true;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isModuleEnabled);
        $this->configMock->expects($this->once())
            ->method('getProcessBirthdaysLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->with(EventInterface::TYPE_CUSTOMER_BIRTHDAY)
            ->willThrowException(new \Exception('Error!'));

        $this->configMock->expects($this->once())
            ->method('setProcessBirthdaysLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

        $this->assertSame($this->model, $this->model->execute());
    }

    /**
     * Test execute method if event disabled
     */
    public function testExecuteEventDisabled()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 86000;
        $isModuleEnabled = true;
        $isTypeEnabled = false;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isModuleEnabled);
        $this->configMock->expects($this->once())
            ->method('getProcessBirthdaysLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isTypeEnabled);

        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->with(EventInterface::TYPE_CUSTOMER_BIRTHDAY)
            ->willReturn($eventTypeMock);

        $this->configMock->expects($this->once())
            ->method('setProcessBirthdaysLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

        $this->assertSame($this->model, $this->model->execute());
    }

    /**
     * Test execute method if no handler found
     */
    public function testExecuteNoHandler()
    {
        $lastExecTimestamp = 1483228801;
        $currentTimestamp = 1483228801 + 86000;
        $isModuleEnabled = true;
        $isTypeEnabled = true;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isModuleEnabled);
        $this->configMock->expects($this->once())
            ->method('getProcessBirthdaysLastExecTime')
            ->willReturn($lastExecTimestamp);
        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('timestamp')
            ->willReturn($currentTimestamp);

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isTypeEnabled);
        $eventTypeMock->expects($this->once())
            ->method('getHandler')
            ->willReturn(null);

        $this->eventTypePoolMock->expects($this->once())
            ->method('getType')
            ->with(EventInterface::TYPE_CUSTOMER_BIRTHDAY)
            ->willReturn($eventTypeMock);

        $this->configMock->expects($this->once())
            ->method('setProcessBirthdaysLastExecTime')
            ->with($currentTimestamp)
            ->willReturnSelf();

        $this->assertSame($this->model, $this->model->execute());
    }
}
