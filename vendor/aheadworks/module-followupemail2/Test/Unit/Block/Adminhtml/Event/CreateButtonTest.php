<?php
namespace Aheadworks\Followupemail2\Test\Unit\Block\Adminhtml\Event;

use Aheadworks\Followupemail2\Block\Adminhtml\Event\CreateButton;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Block\Adminhtml\Event\CreateButton
 */
class CreateButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreateButton
     */
    private $button;

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

        $this->eventTypePoolMock = $this->getMockBuilder(EventTypePool::class)
            ->setMethods(['getAllEnabledTypes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->button = $objectManager->getObject(
            CreateButton::class,
            [
                'eventTypePool' => $this->eventTypePoolMock,
            ]
        );
    }

    /**
     * Test getButtonData method
     */
    public function testGetButtonData()
    {
        $eventTitle = 'Abandoned Cart';

        $eventTypeMock = $this->getMockForAbstractClass(EventTypeInterface::class);
        $eventTypeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($eventTitle);

        $this->eventTypePoolMock->expects($this->once())
            ->method('getAllEnabledTypes')
            ->willReturn([$eventTypeMock]);

        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
