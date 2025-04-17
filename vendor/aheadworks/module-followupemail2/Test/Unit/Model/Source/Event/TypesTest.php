<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Source\Event\Types;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Event\Types
 */
class TypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Types
     */
    private $model;

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
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Types::class,
            [
                'eventTypePool' => $this->eventTypePoolMock,
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $code = EventInterface::TYPE_ABANDONED_CART;
        $title = __('Abandoned Cart');
        $result = [
            ['value' => $code, 'label' => $title]
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getAllEnabledTypes')
            ->willReturn([$code => $eventTypeMock]);

        $this->assertSame($result, $this->model->toOptionArray());
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $code = EventInterface::TYPE_ABANDONED_CART;
        $title = __('Abandoned Cart');
        $result = [
            $code => $title
        ];

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getAllEnabledTypes')
            ->willReturn([$code => $eventTypeMock]);

        $this->assertSame($result, $this->model->getOptions());
    }

    /**
     * Test getOptionByValue method
     */
    public function testGetOptionByValue()
    {
        $code = EventInterface::TYPE_ABANDONED_CART;
        $title = __('Abandoned Cart');

        $eventTypeMock = $this->getMockBuilder(EventTypeInterface::class)
            ->getMockForAbstractClass();
        $eventTypeMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $this->eventTypePoolMock->expects($this->once())
            ->method('getAllEnabledTypes')
            ->willReturn([$code => $eventTypeMock]);

        $this->assertSame($title, $this->model->getOptionByValue($code));
    }
}
