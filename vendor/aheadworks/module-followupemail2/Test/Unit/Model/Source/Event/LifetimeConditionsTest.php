<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Queue;

use Aheadworks\Followupemail2\Model\Source\Event\LifetimeConditions;
use Aheadworks\Followupemail2\Model\Event\LifetimeCondition;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Event\LifetimeConditions
 */
class LifetimeConditionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LifetimeConditions
     */
    private $model;

    /**
     * @var LifetimeCondition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lifetimeConditionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->lifetimeConditionMock = $this->getMockBuilder(LifetimeCondition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            LifetimeConditions::class,
            [
                'lifetimeCondition' => $this->lifetimeConditionMock,
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $operator = 'lt';
        $label = __('less than');
        $result = [
            ['value' => $operator, 'label' => $label]
        ];

        $this->lifetimeConditionMock->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([$operator => $label]);

        $this->assertSame($result, $this->model->toOptionArray());
    }
}
